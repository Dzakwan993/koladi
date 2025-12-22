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
use Illuminate\Support\Facades\Log;

class WorkspaceController extends Controller
{
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

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompany->id)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            $workspaces = Workspace::with(['creator', 'userWorkspaces.user', 'userWorkspaces.role'])
                ->where('company_id', $activeCompany->id)
                ->active()
                ->get()
                ->groupBy('type');
        } else {
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
            'userRole' => $userRole
        ]);
    }

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
                'message' => 'Anda tidak memiliki izin untuk membuat workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat membuat workspace.'
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

            // ✅ CEK ONBOARDING (dari main branch)
            $showOnboarding = false;
            if ($user->onboarding_step === 'kelola-workspace') {
                $user->onboarding_step = 'workspace-created';
                $user->save();
                $showOnboarding = true;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workspace berhasil dibuat!',
                'workspace' => $workspace,
                'show_onboarding' => $showOnboarding,
                'workspace_name' => $workspace->name
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:HQ,Tim,Proyek',
            'description' => 'nullable|string'
        ]);

        $workspace = Workspace::findOrFail($id);

        if (!$this->canEditDeleteWorkspace()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat mengedit workspace.'
            ], 403);
        }

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

    public function destroy($id)
    {
        $workspace = Workspace::findOrFail($id);

        if (!$this->canEditDeleteWorkspace()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus workspace. Hanya SuperAdmin, Admin, dan Manager yang dapat menghapus workspace.'
            ], 403);
        }

        if (!$this->checkWorkspaceAccess($workspace)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke workspace ini'], 403);
        }

        $workspace->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workspace berhasil dihapus!'
        ]);
    }

    public function manageMembers(Request $request, $workspaceId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $workspace = Workspace::findOrFail($workspaceId);

        if (!$this->canManageWorkspaceMembers($workspace)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengelola anggota workspace. Hanya SuperAdmin, Admin, Manager di company, atau Manager di workspace yang dapat mengelola anggota.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $validUserIds = User::whereHas('userCompanies', function ($query) use ($workspace) {
                $query->where('company_id', $workspace->company_id)
                    ->where('status_active', true)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', 'Member');
                    });
            })->whereIn('id', $request->user_ids)->pluck('id')->toArray();

            UserWorkspace::where('workspace_id', $workspaceId)
                ->whereNotIn('user_id', $validUserIds)
                ->delete();

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

    public function getMembers($workspaceId)
    {
        $workspace = Workspace::with(['userWorkspaces.user', 'userWorkspaces.role'])->findOrFail($workspaceId);

        if (!$this->canManageWorkspaceMembers($workspace)) {
            return response()->json([
                'error' => 'Anda tidak memiliki akses untuk melihat anggota workspace ini'
            ], 403);
        }

        $members = $workspace->userWorkspaces->map(function ($userWorkspace) {
            $user = $userWorkspace->user;

            // ✅ LOGIC AVATAR LENGKAP (dari main branch)
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
                'avatar' => $avatarUrl
            ];
        });

        return response()->json($members);
    }

    public function getAvailableUsers()
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            if (!$activeCompanyId) {
                return response()->json(['error' => 'No active company'], 400);
            }

            $companyUsers = User::whereHas('userCompanies', function ($query) use ($activeCompanyId) {
                $query->where('company_id', $activeCompanyId)
                    ->where('status_active', true)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('name', 'Member');
                    });
            })->get();

            $users = $companyUsers->map(function ($user) {
                // ✅ LOGIC AVATAR YANG BENAR (sama seperti di getMembers)
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
                    'avatar' => $avatarUrl // ✅ GUNAKAN AVATAR DARI DATABASE
                ];
            });

            return response()->json($users);
        } catch (\Exception $e) {
            Log::error('Error in getAvailableUsers: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    private function checkWorkspaceAccess($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if ($workspace->created_by === $user->id) {
            return true;
        }

        if ($activeCompanyId && $workspace->company_id !== $activeCompanyId) {
            return false;
        }

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->first();

        return !is_null($userWorkspace);
    }

    public function show(Workspace $workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if (!$this->canAccessWorkspace($workspace)) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini');
        }

        session([
            'current_workspace_id' => $workspace->id,
            'current_workspace_name' => $workspace->name
        ]);

        // ✅ CEK ONBOARDING (dari main branch)
        $showOnboarding = false;
        if ($user->onboarding_step === 'workspace-created') {
            $showOnboarding = true;
        }

        return view('workspace', [
            'workspace' => $workspace,
            'showOnboarding' => $showOnboarding
        ]);
    }

    private function canAccessWorkspace($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        if ($workspace->created_by === $user->id) {
            return true;
        }

        if ($activeCompanyId && $workspace->company_id !== $activeCompanyId) {
            return false;
        }

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->first();

        return !is_null($userWorkspace);
    }

    private function canManageWorkspaceMembers($workspace)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userCompanyRole = $userCompany?->role?->name ?? 'Member';

        if (in_array($userCompanyRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->with('role')
            ->first();

        $userWorkspaceRole = $userWorkspace?->role?->name ?? 'Member';

        return $userWorkspaceRole === 'Manager';
    }

    private function canEditDeleteWorkspace()
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        return in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
    }

    // ✅ METHOD INI DARI MAIN BRANCH
    public function getAvailableRolesForWorkspace()
    {
        $roles = Role::whereIn('id', [
            'a688ef38-3030-45cb-9a4d-0407605bc322', // Manager
            'ed81bd39-9041-43b8-a504-bf743b5c2919'  // Member
        ])->get(['id', 'name']);

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
