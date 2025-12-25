<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserCompany;

class CheckUserCompanyStatus
{
    /**
     * Handle an incoming request.
     * 
     * Middleware ini akan:
     * 1. Cek apakah user login
     * 2. Cek apakah ada active_company_id di session
     * 3. Cek status_active user di company tersebut
     * 4. Jika false, logout dan redirect ke login dengan pesan error
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip jika user belum login
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // Skip jika tidak ada active company (misalnya baru login belum pilih company)
        if (!$activeCompanyId) {
            return $next($request);
        }

        // Cek status user di company
        $userCompany = UserCompany::where('user_id', $user->id)
            ->where('company_id', $activeCompanyId)
            ->first();

        // Jika user tidak ditemukan di company atau status_active = false
        if (!$userCompany || $userCompany->status_active === false) {
            
            // Logout user
            Auth::logout();
            
            // Hapus semua session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect ke login dengan pesan error
            return redirect()->route('masuk')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan oleh Administrator. Hubungi admin untuk informasi lebih lanjut.',
            ]);
        }

        return $next($request);
    }
}