<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $user = Auth::user();

            if ($user) {
                $companies = $user->companies ?? collect();
                $activeCompany = session('active_company_id')
                    ? Company::find(session('active_company_id'))
                    : $companies->first();

                // 🔹 Tambahkan avatar default di sini
                if ($user->avatar && Str::startsWith($user->avatar, ['http://', 'https://'])) {
                    $avatar = $user->avatar;
                } elseif ($user->avatar) {
                    $avatar = asset('storage/' . $user->avatar);
                } else {
                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user->full_name ?? $user->name ?? 'User') . '&background=4F46E5&color=fff&bold=true';
                }

                // 🔹 Kirim semua ke view
                $view->with([
                    'user' => $user,
                    'companies' => $companies,
                    'activeCompany' => $activeCompany,
                    'avatar' => $avatar, // <--- ini penting
                ]);
            }
        });
    }
}
