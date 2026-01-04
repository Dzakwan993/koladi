<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\UserCompany;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('Middleware CheckSuperAdmin dijalankan untuk URL: ' . $request->url());
        
        $companyId = session('active_company_id');

        if (!$companyId) {
            $userCompany = UserCompany::where('user_id', auth()->id())->first();
            if (!$userCompany) {
                return redirect()->route('dashboard')
                    ->with('error-pembayaran', 'Anda belum terdaftar di perusahaan manapun.');
            }
            $companyId = $userCompany->company_id;
            session(['active_company_id' => $companyId]);
        }

        $userCompany = UserCompany::where('user_id', auth()->id())
            ->where('company_id', $companyId)
            ->where('status_active', true)
            ->with('role')
            ->first();

        if (!$userCompany || !$userCompany->role) {
            return redirect()->route('dashboard')
                ->with('error-pembayaran', 'Role Anda tidak ditemukan di perusahaan ini.');
        }

        $roleName = strtolower($userCompany->role->name);

        if ($roleName !== 'superadmin') {
            return redirect()->back()
                ->with('error-pembayaran', 'Anda tidak memiliki akses ke halaman pembayaran.');
        }

        return $next($request);
    }
}