<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use App\Http\View\Composers\UserRoleComposer;  // ← DAN INI!

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register view composer untuk component atur-hak
        View::composer('components.atur-hak', UserRoleComposer::class);
    }
}
