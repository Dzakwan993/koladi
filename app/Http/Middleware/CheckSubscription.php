<?php

namespace App\Http\Middleware;


use Closure;
use Carbon\Carbon;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('masuk');
        }

        $excludedRoutes = [
            'pembayaran',
            'api.plans',
            'subscription.create',
            'midtrans.callback',
            'logout',
            'profile.index',
            'profile.update',
            'profile.avatar.update',
            'access.blocked',
            'buat-perusahaan.create',
            'buat-perusahaan',
            'dashboard',
            'company.switch',
        ];

        if (in_array($request->route()->getName(), $excludedRoutes)) {
            return $next($request);
        }

        $companyId = session('active_company_id');

        if (!$companyId) {
            return redirect()->route('buat-perusahaan.create')
                ->with('info', 'Silakan buat perusahaan terlebih dahulu');
        }

        $company = Company::with('subscription')->find($companyId);

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'Perusahaan tidak ditemukan');
        }

        // ✅ CEK SUBSCRIPTION DULU (PRIORITAS LEBIH TINGGI)
        $subscriptionActive = false;
        if ($company->subscription) {
            $subscriptionActive = $company->subscription->status === 'active' &&
                Carbon::parse($company->subscription->end_date)->isFuture();
        }

        // ✅ JIKA SUBSCRIPTION AKTIF, LANGSUNG ALLOW
        if ($subscriptionActive) {
            // Update status company jika masih trial
            if ($company->status === 'trial') {
                $company->update(['status' => 'active']);
            }

            // Cek user limit
            $userCount = $company->users()->count();
            $userLimit = $company->subscription->total_user_limit;

            if ($userCount > $userLimit) {
                session()->flash('warning', 'Jumlah anggota melebihi batas paket. Silakan upgrade paket atau kurangi anggota.');
            }

            return $next($request);
        }

        // ✅ JIKA TIDAK ADA SUBSCRIPTION AKTIF, BARU CEK TRIAL
        $trialActive = false;
        if ($company->status === 'trial' && $company->trial_end) {
            $trialEnds = Carbon::parse($company->trial_end);
            $trialActive = $trialEnds->isFuture();
        }

        // ✅ JIKA TRIAL AKTIF, ALLOW
        if ($trialActive) {
            return $next($request);
        }

        // ❌ JIKA TIDAK ADA YANG AKTIF, BLOCK
        $company->update(['status' => 'expired']);

        return redirect()->route('pembayaran')
            ->with('error', 'Paket Anda telah berakhir. Silakan pilih paket untuk melanjutkan.');
    }
}
