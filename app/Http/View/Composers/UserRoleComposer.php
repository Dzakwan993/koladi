<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRoleComposer
{
    public function compose(View $view)
    {
        $activeCompanyId = session('active_company_id');
        
        if ($activeCompanyId) {
            $company = Company::with(['users' => function($query) {
                $query->withPivot('roles_id');
            }])->find($activeCompanyId);

            if ($company) {
                $users = $company->users->map(function($user) {
                    $user->current_role = Role::find($user->pivot->roles_id);
                    return $user;
                });
            } else {
                $users = collect([]);
            }
            
            $activeCompany = $company;
        } else {
            $activeCompany = null;
            $users = collect([]);
        }

        // ✅ TAMBAHKAN: Cek apakah user yang login punya akses
        $currentUser = Auth::user();
        $currentUserRole = $this->getCurrentUserRole($currentUser, $activeCompanyId);
        $canManageRoles = $this->canManageRoles($currentUserRole);

        // Ambil semua roles kecuali Super Admin untuk dropdown
        $availableRoles = Role::whereNotIn('name', ['Super Admin'])->get();

        $view->with('activeCompany', $activeCompany)
             ->with('usersInCompany', $users)
             ->with('availableRoles', $availableRoles)
             ->with('canManageRoles', $canManageRoles); // ✅ TAMBAHKAN INI
    }

    // ✅ TAMBAHKAN: Method untuk mendapatkan role user saat ini
    private function getCurrentUserRole($user, $companyId)
    {
        if (!$user || !$companyId) {
            return null;
        }

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