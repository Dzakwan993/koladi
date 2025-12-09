<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // <- TAMBAH INI

class WorkspaceController extends Controller
{
    // Menampilkan halaman kelola workspace

    // Di WorkspaceController.php - modifikasi method index()
    // Di WorkspaceController.php - modifikasi method index()
    public function index()
    {
        $user = Auth::user();
        $activeCompany = session('active_company_id')
            ? Company::find(session('active_company_id'))
            : $user->companies->first();

        if (!$activeCompany) {
            return redirect()->route('buat-perusahaan.create')
                ->with('error', 'Silakan buat perusahaan terlebih dahulu.');
        }

        // ✅ CEK ROLE USER DI COMPANY
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompany->id)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        // ✅ JIKA SUPERADMIN/ADMIN/MANAGER, TAMPILKAN SEMUA WORKSPACE DI COMPANY
        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            $workspaces = Workspace::with(['creator', 'userWorkspaces.user', 'userWorkspaces.role'])
                ->where('company_id', $activeCompany->id)
                ->active()
                ->get()
                ->groupBy('type');
        }
        // ✅ JIKA BUKAN SUPERADMIN/ADMIN/MANAGER, TAMPILKAN HANYA WORKSPACE YANG DIIKUTI
        else {
            $workspaces = Workspace::with(['creator', 'userWorkspaces.user', 'userWorkspaces.role'])
                ->where('company_id', $activeCompany->id)
                ->active()
                ->whereHas('userWorkspaces', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('status_active', true);
                })
                ->get()
                ->groupBy('type');
        }

        $roles = Role::select('id', 'name')->get();

        return view('kelola-workspace', [
            'workspaces' => $workspaces,
            'activeCompany' => $activeCompany,
            'roles' => $roles,
            'userRole' => $userRole // ✅ KIRIM USER ROLE KE VIEW
        ]);
    }

    // Menyimpan workspace baru
    // app/Http/Controllers/WorkspaceController.php

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HQ,Tim,Proyek',
            'description' => 'nullable|string'
        ]);

        $activeCompanyId = session('active_company_id');
        if (!$activeCompanyId) {
            return response()->json(['error' => 'Tidak ada perusahaan yang aktif'], 400);
        }

        $user = Auth::user();
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (!in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membuat workspace.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $workspace = Workspace::create([
                'company_id' => $activeCompanyId,
                'type' => $request->type,
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            // ✅ 🎯 CEK ONBOARDING
            $showOnboarding = false;
            if ($user->onboarding_step === 'kelola-workspace') {
                $user->onboarding_step = 'workspace-created';
                $user->save();
                $showOnboarding = true; // ✅ Flag untuk trigger modal
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workspace berhasil dibuat!',
                'workspace' => $workspace,
                'show_onboarding' => $showOnboarding, // ✅ Kirim flag
                'workspace_name' => $workspace->name // ✅ Untuk ditampilkan di modal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workspace creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat workspace'
            ], 500);
        }
    }

    // Update workspace
    // Di WorkspaceController.php - update method update()
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HQ,Tim,Proyek',
            'description' => 'nullable|string'
        ]);

        $workspace = Workspace::findOrFail($id);

        // ✅ CEK APAKAH USER BOLEH EDIT WORKSPACE
        if (!$this->canEditDeleteWorkspace()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat mengedit workspace.'
            ], 403);
        }

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        $workspace->update([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workspace berhasil diperbarui!',
            'workspace' => $workspace
        ]);
    }

    // Di WorkspaceController.php - update method updateUserRoles()
    // Di WorkspaceController.php - update method updateUserRoles()
    public function updateUserRoles(Request $request, $workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);

        // ✅ CEK APAKAH USER BOLEH MENGUBAH ROLE WORKSPACE
        if (!$this->canManageWorkspaceMembers($workspace)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah role workspace. Hanya SuperAdmin, Admin, Manager di company, atau Manager di workspace yang dapat mengubah role.'
            ], 403);
        }

        $request->validate([
            'changes' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->input('changes') as $userId => $roleId) {
                UserWorkspace::where('workspace_id', $workspaceId)
                    ->where('user_id', $userId)
                    ->update(['roles_id' => $roleId, 'updated_at' => now()]);
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Role workspace berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Hapus workspace
    // Di WorkspaceController.php - update method destroy()
    public function destroy($id)
    {
        $workspace = Workspace::findOrFail($id);

        // ✅ CEK APAKAH USER BOLEH HAPUS WORKSPACE
        if (!$this->canEditDeleteWorkspace()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat menghapus workspace.'
            ], 403);
        }

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        $workspace->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workspace berhasil dihapus!'
        ]);
    }

    // Kelola anggota workspace
    // Di WorkspaceController.php - update method manageMembers()
    // Di WorkspaceController.php - update method manageMembers()
    public function manageMembers(Request $request, $workspaceId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $workspace = Workspace::findOrFail($workspaceId);

        // ✅ CEK APAKAH USER BOLEH MENGELOLA ANGGOTA WORKSPACE
        if (!$this->canManageWorkspaceMembers($workspace)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengelola anggota workspace. Hanya SuperAdmin, Admin, Manager di company, atau Manager di workspace yang dapat mengelola anggota.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // ✅ VALIDASI: Pastikan hanya user dengan role Member di company yang bisa ditambahkan
            $validUserIds = User::whereHas('userCompanies', function ($query) use ($workspace) {
                $query->where('company_id', $workspace->company_id)
                    ->where('status_active', true)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', 'Member'); // Hanya user dengan role Member
                    });
            })->whereIn('id', $request->user_ids)->pluck('id')->toArray();

            // Hapus anggota lama yang tidak dipilih
            UserWorkspace::where('workspace_id', $workspaceId)
                ->whereNotIn('user_id', $validUserIds)
                ->delete();

            // Tambah/update anggota baru
            foreach ($validUserIds as $userId) {
                UserWorkspace::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'workspace_id' => $workspaceId
                    ],
                    [
                        'roles_id' => $request->role_id,
                        'status_active' => true
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Anggota workspace berhasil diupdate!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Manage members error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate anggota: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get anggota workspace
    public function getMembers($workspaceId)
    {
        $workspace = Workspace::with(['userWorkspaces.user', 'userWorkspaces.role'])->findOrFail($workspaceId);

        // ✅ CEK APAKAH USER BOLEH MELIHAT/MENGELOLA ANGGOTA WORKSPACE
        if (!$this->canManageWorkspaceMembers($workspace)) {
            return response()->json([
                'error' => 'Anda tidak memiliki akses untuk melihat anggota workspace ini'
            ], 403);
        }

        $members = $workspace->userWorkspaces->map(function ($userWorkspace) {
            $user = $userWorkspace->user;

            // ✅ LOGIC AVATAR YANG BENAR
            if ($user->avatar && \Str::startsWith($user->avatar, ['http://', 'https://'])) {
                $avatarUrl = $user->avatar;
            } elseif ($user->avatar) {
                $avatarUrl = asset('storage/' . $user->avatar);
            } else {
                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? 'User') . '&background=4F46E5&color=fff&bold=true';
            }

            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $userWorkspace->role->name,
                'avatar' => $avatarUrl // ✅ GUNAKAN AVATAR URL YANG BENAR
            ];
        });

        return response()->json($members);
    }

    // Di WorkspaceController - method getAvailableUsers()
    public function getAvailableUsers()
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            if (!$activeCompanyId) {
                return response()->json(['error' => 'No active company'], 400);
            }

            // ✅ FILTER: Ambil users hanya dari company yang aktif DENGAN ROLE MEMBER
            $companyUsers = User::whereHas('userCompanies', function ($query) use ($activeCompanyId) {
                $query->where('company_id', $activeCompanyId)
                    ->where('status_active', true) // hanya yang aktif
                    ->whereHas('role', function ($roleQuery) {
                        // ✅ HANYA tampilkan Member saja (SuperAdmin, Admin, Manager tidak ditampilkan)
                        $roleQuery->where('name', 'Member');
                    });
            })->get();

            $users = $companyUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70))
                ];
            });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableUsers: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // Cek akses user ke workspace
    // Cek akses user ke workspace
    private function checkWorkspaceAccess($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // Jika user adalah pembuat workspace => selalu boleh
        if ($workspace->created_by === $user->id) {
            return true;
        }

        // Jika ada active company dalam session dan workspace bukan milik company aktif => tolak
        if ($activeCompanyId && $workspace->company_id !== $activeCompanyId) {
            return false;
        }

        // ✅ CEK APAKAH USER ADALAH SUPERADMIN/ADMIN/MANAGER DI COMPANY
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        // ✅ JIKA SUPERADMIN/ADMIN/MANAGER, BOLEH AKSES SEMUA WORKSPACE DI COMPANY
        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        // ✅ JIKA BUKAN, CEK APAKAH USER ADALAH ANGGOTA WORKSPACE
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->first();

        return !is_null($userWorkspace);
    }

    // app/Http/Controllers/WorkspaceController.php

    public function show(Workspace $workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ✅ CEK APAKAH USER BOLEH AKSES WORKSPACE INI
        if (!$this->canAccessWorkspace($workspace)) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini');
        }

        // Simpan workspace yang dipilih di session
        session([
            'current_workspace_id' => $workspace->id,
            'current_workspace_name' => $workspace->name
        ]);

        // ✅ 🎯 CEK APAKAH INI WORKSPACE BARU (untuk onboarding)
        $showOnboarding = false;
        if ($user->onboarding_step === 'workspace-created') {
            $showOnboarding = true;
        }

        return view('workspace', [
            'workspace' => $workspace,
            'showOnboarding' => $showOnboarding // ✅ Kirim flag ke view
        ]);
    }

    // ✅ TAMBAHKAN METHOD UNTUK CEK AKSES WORKSPACE
    // ✅ TAMBAHKAN METHOD UNTUK CEK AKSES WORKSPACE
    private function canAccessWorkspace($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // Jika user adalah pembuat workspace => selalu boleh
        if ($workspace->created_by === $user->id) {
            return true;
        }

        // Jika ada active company dalam session dan workspace bukan milik company aktif => tolak
        if ($activeCompanyId && $workspace->company_id !== $activeCompanyId) {
            return false;
        }

        // ✅ CEK APAKAH USER ADALAH SUPERADMIN/ADMIN/MANAGER DI COMPANY
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        // ✅ JIKA SUPERADMIN/ADMIN/MANAGER, BOLEH AKSES SEMUA WORKSPACE DI COMPANY
        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        // ✅ JIKA BUKAN, CEK APAKAH USER ADALAH ANGGOTA WORKSPACE
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->first();

        return !is_null($userWorkspace);
    }


    // Di WorkspaceController.php - tambahkan method ini
    // Di WorkspaceController.php - tambahkan method ini
    private function canManageWorkspaceMembers($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ✅ CEK ROLE USER DI COMPANY
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userCompanyRole = $userCompany?->role?->name ?? 'Member';

        // ✅ JIKA SUPERADMIN/ADMIN/MANAGER DI COMPANY, BOLEH KELOLA ANGGOTA SEMUA WORKSPACE
        if (in_array($userCompanyRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        // ✅ JIKA BUKAN, CEK APAKAH USER ADALAH MANAGER DI WORKSPACE
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->with('role')
            ->first();

        $userWorkspaceRole = $userWorkspace?->role?->name ?? 'Member';

        // ✅ HANYA MANAGER DI WORKSPACE YANG BOLEH KELOLA ANGGOTA
        return $userWorkspaceRole === 'Manager';
    }

    // Di WorkspaceController.php - tambahkan method ini
    private function canEditDeleteWorkspace()
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ✅ CEK ROLE USER DI COMPANY
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        // ✅ HANYA SuperAdmin, Administrator, Admin, dan Manager yang boleh edit/hapus workspace
        return in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
    }

    // ✅ TAMBAHKAN METHOD INI DI AKHIR CLASS
    public function getAvailableRolesForWorkspace()
    {
        // Untuk workspace, hanya Manager dan Member yang tersedia
        $roles = Role::whereIn('id', [
            'a688ef38-3030-45cb-9a4d-0407605bc322', // Manager
            'ed81bd39-9041-43b8-a504-bf743b5c2919'  // Member
        ])->get(['id', 'name']);

        // Fallback manual jika query gagal
        if ($roles->count() === 0) {
            $roles = collect([
                (object)[
                    'id' => 'a688ef38-3030-45cb-9a4d-0407605bc322',
                    'name' => 'Manager',
                ],
                (object)[
                    'id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919',
                    'name' => 'Member',
                ]
            ]);
        }

        Log::info('Available roles for workspace:', [
            'count' => $roles->count(),
            'roles' => $roles->pluck('name')->toArray()
        ]);

        return response()->json($roles);
    }
}
