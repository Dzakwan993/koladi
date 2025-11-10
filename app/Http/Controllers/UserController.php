<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\UserWorkspace;

class UserController extends Controller
{
    public function hakAkses()
    {
        \Log::info('=== HAK AKSES METHOD START ===');
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            abort(400, 'Tidak ada perusahaan yang aktif');
        }

        // ✅ DEBUG: Cek session dan auth
        \Log::info('Session and Auth check:', [
            'active_company_id' => $activeCompanyId,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name
        ]);

        // ✅ DEBUG: Cek semua roles yang ada di database dengan cara berbeda
        $allRoles = Role::all();
        \Log::info('ALL ROLES IN DATABASE:', $allRoles->pluck('name', 'id')->toArray());

        // ✅ PERBAIKAN: Ambil company dengan relasi users dan role mereka
        $activeCompany = Company::with(['users' => function ($query) use ($activeCompanyId) {
            $query->with(['userCompanies' => function ($q) use ($activeCompanyId) {
                $q->where('company_id', $activeCompanyId)->with('role');
            }]);
        }])->find($activeCompanyId);

        \Log::info('Active Company:', [
            'company_id' => $activeCompanyId,
            'company_name' => $activeCompany->name ?? 'Not found',
            'users_count' => $activeCompany ? $activeCompany->users->count() : 0
        ]);

        // ✅ PERBAIKAN: Ambil users dengan role mereka
        $usersInCompany = $activeCompany ? $activeCompany->users->map(function ($user) use ($activeCompanyId) {
            $userCompany = $user->userCompanies->where('company_id', $activeCompanyId)->first();
            $user->current_role = $userCompany->role ?? null;
            return $user;
        }) : collect([]);

        // ✅ PERBAIKAN: Cek akses dengan benar
        $currentUser = auth()->user();
        $currentUserRoleObj = $this->getCurrentUserRole($currentUser, $activeCompanyId);

        \Log::info('Current user role object:', [
            'role_obj' => $currentUserRoleObj,
            'role_name' => $currentUserRoleObj ? $currentUserRoleObj->name : 'null'
        ]);

        if (!$currentUserRoleObj || !$this->canManageRoles($currentUserRoleObj)) {
            \Log::warning('User tidak memiliki akses untuk mengatur role', [
                'user_id' => $currentUser->id,
                'role' => $currentUserRoleObj ? $currentUserRoleObj->name : 'null'
            ]);
            abort(403, 'Anda tidak memiliki akses untuk mengatur role');
        }

        $currentUserRole = $currentUserRoleObj->name;

        // ✅ PERBAIKAN KUNCI: Sesuaikan available roles berdasarkan hierarki
        \Log::info('Current user role for filtering:', ['role' => $currentUserRole]);

        // ✅ PERBAIKAN: Atur available roles berdasarkan hierarki role
        if (in_array($currentUserRole, ['SuperAdmin', 'Administrator'])) {
            \Log::info('Using roles for SuperAdmin/Administrator');
            
            // ✅ PERUBAHAN: Hanya Manager dan Member, TANPA AdminSistem
            $availableRoles = Role::whereIn('id', [
                'a688ef38-3030-45cb-9a4d-0407605bc322', // Manager
                'ed81bd39-9041-43b8-a504-bf743b5c2919'  // Member
            ])->get();
            
            \Log::info('Available roles for SuperAdmin/Administrator:', [
                'count' => $availableRoles->count(),
                'roles' => $availableRoles->pluck('name')->toArray()
            ]);
            
            // Method 2: Jika masih kosong, buat manual collection TANPA AdminSistem
            if ($availableRoles->count() === 0) {
                \Log::warning('Query roles failed, creating manual collection');
                $availableRoles = collect([
                    (object)[
                        'id' => 'a688ef38-3030-45cb-9a4d-0407605bc322', 
                        'name' => 'Manager'
                    ],
                    (object)[
                        'id' => 'ed81bd39-9041-43b8-a504-bf743b5c2919', 
                        'name' => 'Member'
                    ]
                ]);
                \Log::info('Manual collection created:', $availableRoles->pluck('name')->toArray());
            }
            
        } else if ($currentUserRole === 'AdminSistem') {
            \Log::info('Using roles for AdminSistem');
            
            // AdminSistem hanya bisa atur Manager & Member
            $availableRoles = Role::whereIn('id', [
                'a688ef38-3030-45cb-9a4d-0407605bc322', // Manager
                'ed81bd39-9041-43b8-a504-bf743b5c2919'  // Member
            ])->get();
            
            \Log::info('Available roles for AdminSistem:', [
                'count' => $availableRoles->count(),
                'roles' => $availableRoles->pluck('name')->toArray()
            ]);
            
        } else {
            // Manager & Member tidak bisa ubah role
            $availableRoles = collect();
            \Log::info('Other role path - no available roles');
        }

        \Log::info('FINAL AVAILABLE ROLES:', [
            'count' => $availableRoles->count(),
            'roles' => $availableRoles->pluck('name')->toArray(),
            'currentUserRole' => $currentUserRole
        ]);

        \Log::info('=== HAK AKSES METHOD END ===');

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

            // ✅ TAMBAHKAN: Cek apakah user punya akses untuk mengatur role
            if (!$this->canManageRoles($currentUserRole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengatur role'
                ], 403);
            }

            $changes = $request->input('changes');
            $companyId = $request->input('company_id');

            foreach ($changes as $userId => $roleId) {
                DB::table('user_companies')
                    ->where('user_id', $userId)
                    ->where('company_id', $companyId)
                    ->update([
                        'roles_id' => $roleId,
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

    // ✅ PERBAIKAN: Method untuk mendapatkan role user saat ini
    private function getCurrentUserRole($user, $companyId)
    {
        $userCompany = DB::table('user_companies')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->first();

        if (!$userCompany || !$userCompany->roles_id) {
            \Log::warning('User company record not found or no role_id');
            return null;
        }

        $role = Role::find($userCompany->roles_id);
        return $role;
    }

    // ✅ PERBAIKAN: Method untuk cek apakah user bisa mengatur role
    private function canManageRoles($userRole)
    {
        if (!$userRole) {
            return false;
        }

        // ✅ PERBAIKAN: Sesuaikan dengan nama role yang sebenarnya di database
        return in_array($userRole->name, ['SuperAdmin', 'Administrator', 'AdminSistem']);
    }

    // Di UserController.php - modifikasi method yang sudah ada
public function getWorkspaceUserRole($workspaceId)
{
    $user = auth()->user();
    $activeCompanyId = session('active_company_id');

    // ✅ 1. CEK ROLE DI COMPANY TERLEBIH DAHULU
    $userCompany = $user->userCompanies()
        ->where('company_id', $activeCompanyId)
        ->with('role')
        ->first();

    $companyRole = $userCompany?->role?->name ?? 'Member';

    // ✅ 2. JIKA SUPERADMIN/ADMIN DI COMPANY, GUNAKAN ROLE COMPANY
    if (in_array($companyRole, ['SuperAdmin', 'Admin', 'Super Admin', 'Administrator'])) {
        return response()->json([
            'role' => $companyRole,
            'source' => 'company',
            'is_company_admin' => true
        ]);
    }

    // ✅ 3. JIKA BUKAN, CEK ROLE DI WORKSPACE (LOGICA EXISTING)
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


// Di UserController.php - tambahkan method ini
private function canCreateWorkspace($userRole)
{
    if (!$userRole) {
        return false;
    }

    // ✅ Hanya SuperAdmin, Admin, dan Manager yang boleh buat workspace
    return in_array($userRole->name, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
}

// Method untuk mendapatkan current user role (jika belum ada)
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