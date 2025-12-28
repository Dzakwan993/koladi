<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckWorkspaceAccess;
use App\Http\Middleware\CheckSystemAdmin;
use App\Http\Middleware\CheckUserCompanyStatus; // 🔥 TAMBAHAN BARU

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Alias middleware custom
        $middleware->alias([
            'check.subscription' => CheckSubscription::class,
            'check.workspace' => CheckWorkspaceAccess::class,
            'check.system.admin' => CheckSystemAdmin::class,
            'check.user.status' => CheckUserCompanyStatus::class, // 🔥 TAMBAHAN BARU
        ]);

        // 🔥 TAMBAHAN: Terapkan middleware ke semua route web yang authenticated
        // Ini akan otomatis cek status user setiap kali ada request
        $middleware->appendToGroup('web', CheckUserCompanyStatus::class);

        // ✅ Pengecualian CSRF untuk Midtrans callback
        $middleware->validateCsrfTokens(except: [
            'midtrans/callback',
            'midtrans/*',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();