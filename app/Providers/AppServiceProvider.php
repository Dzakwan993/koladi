<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Http\View\Composers\UserRoleComposer;
use App\Models\Role;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        // ✅ TAMBAHKAN INI - Register NotificationService sebagai singleton
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */

     public function boot(): void
{
    // ✅ View composer untuk semua view - HARUS DI ATAS
    View::composer('*', function ($view) {
        $user = Auth::user();

        if ($user) {
            $companies = $user->companies ?? collect();
            $activeCompany = session('active_company_id')
                ? Company::find(session('active_company_id'))
                : $companies->first();

            // Avatar default
            if ($user->avatar && Str::startsWith($user->avatar, ['http://', 'https://'])) {
                $avatar = $user->avatar;
            } elseif ($user->avatar) {
                $avatar = asset('storage/' . $user->avatar);
            } else {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? $user->name ?? 'User') . '&background=4F46E5&color=fff&bold=true';
            }

            $view->with([
                'user' => $user,
                'companies' => $companies,
                'activeCompany' => $activeCompany,
                'avatar' => $avatar,
            ]);
        }
    });

    // ✅ View composer untuk atur-hak - SETELAH composer *
    View::composer('components.atur-hak', function ($view) {
        $user = Auth::user();

        if (!$user) {
            $view->with([
                'currentUserRole' => 'Guest',
                'availableRoles' => collect(),
                'canManageRoles' => false,
                'usersInCompany' => collect(),
                'activeCompany' => null,
            ]);
            return;
        }

        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $currentUserRole = $userCompany?->role?->name ?? 'Member';
        $canManageRoles = in_array($currentUserRole, ['Super Admin', 'Admin']);

        if ($currentUserRole === 'Super Admin') {
            $availableRoles = Role::whereIn('name', ['Admin', 'Manager', 'Member'])->get();
        } else if ($currentUserRole === 'Admin') {
            $availableRoles = Role::whereIn('name', ['Manager', 'Member'])->get();
        } else {
            $availableRoles = collect();
        }

        $activeCompany = Company::find($activeCompanyId);
        $usersInCompany = $activeCompany
            ? $activeCompany->users()->with('userCompanies.role')->get()
            : collect();

        $usersInCompany->each(function($user) use ($activeCompanyId) {
            $userCompany = $user->userCompanies
                ->where('company_id', $activeCompanyId)
                ->first();
            $user->current_role = $userCompany?->role;
        });

        $view->with([
            'currentUserRole' => $currentUserRole,
            'availableRoles' => $availableRoles,
            'canManageRoles' => $canManageRoles,
            'usersInCompany' => $usersInCompany,
            'activeCompany' => $activeCompany,
        ]);
    });

    // ✅ View composer untuk roles
    view()->composer(['layouts.app', 'components.atur-hak'], function ($view) {
        $view->with('roles', Role::select('id','name')->get());
    });
}
}
