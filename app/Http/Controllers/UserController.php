<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function hakAkses()
    {
        Log::info('=== HAK AKSES METHOD START ===');
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            abort(400, 'Tidak ada perusahaan yang aktif');
        }

        Log::info('Session and Auth check:', [
            'active_company_id' => $activeCompanyId,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        // Ambil semua roles yang ada di database
        $allRoles = Role::all();
        Log::info('ALL ROLES IN DATABASE:', $allRoles->pluck('name', 'id')->toArray());

        // Ambil company dengan relasi users dan role mereka
        $activeCompany = Company::with(['users' => function ($query) use ($activeCompanyId) {
            $query->with(['userCompanies' => function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)->with('role');
            }]);
        }])->find($activeCompanyId);

        Log::info('Active Company:', [
            'company_id' => $activeCompanyId,
            'company_name' => $activeCompany->name ?? 'Not found',
            'users_count' => $activeCompany ? $activeCompany->users->count() : 0
        ]);

        // Ambil users dengan role mereka
        $usersInCompany = $activeCompany ? $activeCompany->users->map(function ($user) use ($activeCompanyId) {
            $userCompany = $user->userCompanies->where('company_id', $activeCompanyId)->first();
            $user->current_role = $userCompany->role ?? null;
            return $user;
        }) : collect([]);

        // Cek akses user saat ini
        $currentUser = auth()->user();
        $currentUserRoleObj = $this->getCurrentUserRole($currentUser, $activeCompanyId);

        Log::info('Current user role object:', [
            'role_obj' => $currentUserRoleObj,
            'role_name' => $currentUserRoleObj ? $currentUserRoleObj->name : 'null'
        ]);

        if (!$currentUserRoleObj || !$this->canManageRoles($currentUserRoleObj)) {
            Log::warning('User tidak memiliki akses untuk mengatur role', [
                'user_id' => $currentUser->id,
                'role' => $currentUserRoleObj ? $currentUserRoleObj->name : 'null'
            ]);
            abort(403, 'Anda tidak memiliki akses untuk mengatur role');
        }

        $currentUserRole = $currentUserRoleObj->name;

        Log::info('Current user role for filtering:', ['role' => $currentUserRole]);

        // Atur available roles berdasarkan hierarki yang benar
        if ($currentUserRole === 'SuperAdmin') {
            Log::info('Using roles for SuperAdmin - CAN MANAGE ALL EXCEPT SuperAdmin');

            $availableRoles = Role::whereIn('name', [
                'Administrator',
                'Manager',
                'Member'
            ])->get();

            if ($availableRoles->count() === 0) {
                Log::warning('Query by name failed, trying by ID');
                $availableRoles = Role::whereIn('id', [
                    '55555555-5555-5555-5555-555555555555',
                    'a688ef38-3030-45cb-9a4d-0407605bc322',
                    'ed81bd39-9041-43b8-a504-bf743b5c2919'
                ])->get();
            }

            if ($availableRoles->count() === 0) {
                Log::warning('Query by ID failed, trying all except SuperAdmin');
                $availableRoles = Role::where('name', '!=', 'SuperAdmin')->get();
            }

            Log::info('Available roles for SuperAdmin:', [
                'count' => $availableRoles->count(),
                'roles' => $availableRoles->pluck('name')->toArray()
            ]);
        } else if ($currentUserRole === 'Administrator') {
            Log::info('Using roles for Administrator - CAN MANAGE Manager & Member');

            $availableRoles = Role::whereIn('name', [
                'Manager',
                'Member'
            ])->get();

            Log::info('Available roles for Administrator:', [
                'count' => $availableRoles->count(),
                'roles' => $availableRoles->pluck('name')->toArray()
            ]);
        } else if ($currentUserRole === 'AdminSistem') {
            Log::info('Using roles for AdminSistem');

            $availableRoles = Role::whereIn('name', [
                'Manager',
                'Member'
            ])->get();

            Log::info('Available roles for AdminSistem:', [
                'count' => $availableRoles->count(),
                'roles' => $availableRoles->pluck('name')->toArray()
            ]);
        } else {
            $availableRoles = collect();
            Log::info('Other role path - no available roles');
        }

        // Fallback manual jika query gagal
        if ($availableRoles->count() === 0 && in_array($currentUserRole, ['SuperAdmin', 'Administrator', 'AdminSistem'])) {
            Log::warning('Available roles is empty, creating manual fallback');

            if ($currentUserRole === 'SuperAdmin') {
                $availableRoles = collect([
                    (object)['id' => '55555555-5555-5555-5555-555555555555', 'name' => 'Administrator', 'created_at' => now(), 'updated_at' => now()],
                    (object)['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
                    (object)['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member', 'created_at' => now(), 'updated_at' => now()]
                ]);
            } else if ($currentUserRole === 'Administrator') {
                $availableRoles = collect([
                    (object)['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
                    (object)['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member', 'created_at' => now(), 'updated_at' => now()]
                ]);
            } else {
                $availableRoles = collect([
                    (object)['id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 'name' => 'Manager', 'created_at' => now(), 'updated_at' => now()],
                    (object)['id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 'name' => 'Member', 'created_at' => now(), 'updated_at' => now()]
                ]);
            }

            Log::info('Manual fallback created:', $availableRoles->pluck('name')->toArray());
        }

        Log::info('FINAL AVAILABLE ROLES:', [
            'count' => $availableRoles->count(),
            'roles' => $availableRoles->pluck('name')->toArray(),
            'currentUserRole' => $currentUserRole
        ]);

        Log::info('=== HAK AKSES METHOD END ===');

        return view('components.atur-hak', compact(
            'activeCompany',
            'usersInCompany',
            'availableRoles',
            'currentUserRole'
        ));
    }

    public function updateUserRoles(Request $request)
    {
        try {
            $activeCompanyId = session('active_company_id');
            $currentUser = auth()->user();
            $currentUserRole = $this->getCurrentUserRole($currentUser, $activeCompanyId);

            if (!$this->canManageRoles($currentUserRole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengatur role'
                ], 403);
            }

            $changes = $request->input('changes');
            $companyId = $request->input('company_id');

            foreach ($changes as $userId => $newRoleId) {
                $newRole = Role::find($newRoleId);

                if ($newRole && $newRole->name === 'SuperAdmin') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat mengubah role menjadi SuperAdmin'
                    ], 403);
                }

                if ($newRole && $newRole->name === 'Administrator' && $currentUserRole->name !== 'SuperAdmin') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya SuperAdmin yang dapat mengatur role Administrator'
                    ], 403);
                }

                DB::table('user_companies')
                    ->where('user_id', $userId)
                    ->where('company_id', $companyId)
                    ->update([
                        'roles_id' => $newRoleId,
                        'updated_at' => now()
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getWorkspaceUserRole($workspaceId)
    {
        $user = auth()->user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';

        if (in_array($companyRole, ['SuperAdmin', 'Admin', 'Super Admin', 'Administrator'])) {
            return response()->json([
                'role' => $companyRole,
                'source' => 'company',
                'is_company_admin' => true
            ]);
        }

        $userWorkspace = $user->userWorkspaces()
            ->where('workspace_id', $workspaceId)
            ->with('role')
            ->first();

        return response()->json([
            'role' => $userWorkspace?->role?->name ?? 'Member',
            'source' => 'workspace',
            'is_company_admin' => false
        ]);
    }

    // ✅ PRIVATE METHODS - Semua di dalam class

    private function getCurrentUserRole($user, $companyId)
    {
        $userCompany = DB::table('user_companies')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->first();

        if (!$userCompany || !$userCompany->roles_id) {
            Log::warning('User company record not found or no role_id');
            return null;
        }

        $role = Role::find($userCompany->roles_id);
        return $role;
    }

    private function canManageRoles($userRole)
    {
        if (!$userRole) {
            return false;
        }

        return in_array($userRole->name, ['SuperAdmin', 'Administrator', 'AdminSistem']);
    }

    private function canCreateWorkspace($userRole)
    {
        if (!$userRole) {
            return false;
        }

        return in_array($userRole->name, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
    }

    private function getCurrentUserCompanyRole()
    {
        $activeCompanyId = session('active_company_id');
        $user = auth()->user();

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        return $userCompany?->role;
    }
}
