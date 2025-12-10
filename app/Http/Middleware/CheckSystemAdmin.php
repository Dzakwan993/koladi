<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('masuk')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!auth()->user()->isSystemAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda bukan Admin Sistem.');
        }

        return $next($request);
    }
}