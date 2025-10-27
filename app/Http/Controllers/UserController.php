<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;

class UserController extends Controller
{
    public function hakAkses()
    {
        $activeCompanyId = session('active_company_id');
        
        // Ambil company dengan relasi users dan role mereka
        $activeCompany = Company::with(['users' => function($query) {
            $query->withPivot('roles_id');
        }])->find($activeCompanyId);

        $users = $activeCompany ? $activeCompany->users : collect([]);

        // ✅ TAMBAHKAN: Cek apakah user yang login punya akses
        $currentUser = auth()->user();
        $currentUserRole = $this->getCurrentUserRole($currentUser, $activeCompanyId);
        
        // ✅ TAMBAHKAN: Cek apakah user punya akses untuk mengatur role
        if (!$this->canManageRoles($currentUserRole)) {
            abort(403, 'Anda tidak memiliki akses untuk mengatur role');
        }

        return view('components.atur-hak', compact('activeCompany', 'users'));
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

    // ✅ TAMBAHKAN: Method untuk mendapatkan role user saat ini
    private function getCurrentUserRole($user, $companyId)
    {
        $userCompany = DB::table('user_companies')
            ->where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->first();

        if (!$userCompany) {
            return null;
        }

        return Role::find($userCompany->roles_id);
    }

    // ✅ TAMBAHKAN: Method untuk cek apakah user bisa mengatur role
    private function canManageRoles($userRole)
    {
        if (!$userRole) {
            return false;
        }

        // Hanya Super Admin dan Admin yang bisa mengatur role
        return in_array($userRole->name, ['Super Admin', 'Admin']);
    }
}