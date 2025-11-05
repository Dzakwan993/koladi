<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // <- TAMBAH INI
use Illuminate\Support\Str;
use App\Models\Workspace;
use App\Models\UserWorkspace;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;

class WorkspaceController extends Controller
{
    // Menampilkan halaman kelola workspace

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

    // Get workspaces grouped by type
    $workspaces = Workspace::with(['creator', 'userWorkspaces.user', 'userWorkspaces.role'])
        ->where('company_id', $activeCompany->id)
        ->active()
        ->get()
        ->groupBy('type');

    $roles = Role::select('id','name')->get();

    return view('kelola-workspace', [
        'workspaces' => $workspaces,
        'activeCompany' => $activeCompany,
        'roles' => $roles
    ]);
}

    // Menyimpan workspace baru
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

        try {
            DB::beginTransaction();

            // Buat workspace
            $workspace = Workspace::create([
                'company_id' => $activeCompanyId,
                'type' => $request->type,
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            // Cari role SuperAdmin, jika tidak ada gunakan role pertama atau buat baru
            $superAdminRole = Role::where('name', 'SuperAdmin')->first();

            if (!$superAdminRole) {
                // Jika SuperAdmin tidak ada, cari role apapun yang tersedia
                $superAdminRole = Role::first();

                // Jika masih tidak ada role, buat role default
                if (!$superAdminRole) {
                    $superAdminRole = Role::create([
                        'id' => Str::uuid()->toString(),
                        'name' => 'SuperAdmin'
                    ]);
                }
            }

            // Tambahkan creator sebagai anggota workspace
            UserWorkspace::create([
                'user_id' => Auth::id(),
                'workspace_id' => $workspace->id,
                'roles_id' => $superAdminRole->id,
                'status_active' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workspace berhasil dibuat!',
                'workspace' => $workspace
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workspace creation error: ' . $e->getMessage()); // <- Ganti \Log menjadi Log
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat workspace: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update workspace
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HQ,Tim,Proyek',
            'description' => 'nullable|string'
        ]);

        $workspace = Workspace::findOrFail($id);

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

    public function updateUserRoles(Request $request, $workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke workspace ini'], 403);
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
    public function destroy($id)
    {
        $workspace = Workspace::findOrFail($id);

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
    public function manageMembers(Request $request, $workspaceId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $workspace = Workspace::findOrFail($workspaceId);

        // Cek apakah user memiliki akses ke workspace ini
        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        try {
            DB::beginTransaction();

            // Hapus anggota lama yang tidak dipilih
            UserWorkspace::where('workspace_id', $workspaceId)
                ->whereNotIn('user_id', $request->user_ids)
                ->delete();

            // Tambah/update anggota baru
            foreach ($request->user_ids as $userId) {
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
            Log::error('Manage members error: ' . $e->getMessage()); // <- Ganti \Log menjadi Log
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate anggota: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get anggota workspace
// Get anggota workspace
public function getMembers($workspaceId)
{
    $workspace = Workspace::with(['userWorkspaces.user', 'userWorkspaces.role'])->findOrFail($workspaceId);

    // Cek apakah user memiliki akses ke workspace ini
    if (!$this->checkWorkspaceAccess($workspace)) {
        return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
    }

    $members = $workspace->userWorkspaces->map(function ($userWorkspace) {
        return [
            'id' => $userWorkspace->user->id,
            'name' => $userWorkspace->user->full_name,
            'email' => $userWorkspace->user->email,
            'role' => $userWorkspace->role->name,
            'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70))
        ];
    });

    return response()->json($members);
}

    // Get users yang available untuk di-add ke workspace
    // Get users yang available untuk di-add ke workspace
public function getAvailableUsers()
{
    try {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return response()->json(['error' => 'No active company'], 400);
        }

        // Ambil users dari company yang aktif
        $companyUsers = User::whereHas('userCompanies', function ($query) use ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
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

        // Cek apakah user adalah anggota aktif dan memiliki role yang diizinkan
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->first();

        if (!$userWorkspace || !$userWorkspace->status_active) {
            return false;
        }

        $roleName = optional($userWorkspace->role)->name;
        return in_array($roleName, ['SuperAdmin', 'Admin']);
    }
}
