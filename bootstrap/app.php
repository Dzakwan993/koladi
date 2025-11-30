<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\CheckWorkspaceAccess;

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
        ]);

        // ✅ Pengecualian CSRF untuk Midtrans callback
        $middleware->validateCsrfTokens(except: [
            'midtrans/callback',
            'midtrans/*',
        ]);

        // ✅ OPTIONAL: Jika ingin apply CheckSubscription ke semua route authenticated
        // Uncomment baris dibawah jika mau otomatis ke semua route
        // $middleware->appendToGroup('web', CheckSubscription::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
