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

        // 🔍 DEBUG
        Log::info('CheckSubscription Middleware:', [
            'company_id' => $company->id,
            'status' => $company->status,
            'trial_start' => $company->trial_start,
            'trial_end' => $company->trial_end,
            'current_time' => Carbon::now(),
        ]);

        $trialActive = false;
        if ($company->status === 'trial' && $company->trial_end) {
            $trialEnds = Carbon::parse($company->trial_end);
            $trialActive = $trialEnds->isFuture();

            // 🔍 DEBUG
            Log::info('Trial Check:', [
                'trial_ends' => $trialEnds,
                'is_future' => $trialActive,
            ]);
        }

        $subscriptionActive = false;
        if ($company->subscription) {
            $subscriptionActive = $company->subscription->status === 'active' &&
                Carbon::parse($company->subscription->end_date)->isFuture();
        }

        if (!$trialActive && !$subscriptionActive) {
            $company->update(['status' => 'expired']);

            return redirect()->route('pembayaran')
                ->with('error', 'Paket Anda telah berakhir. Silakan pilih paket untuk melanjutkan.');
        }


        // ✅ Cek user limit jika ada subscription
        if ($subscriptionActive) {
            $userCount = $company->users()->count();
            $userLimit = $company->subscription->total_user_limit;

            if ($userCount > $userLimit) {
                session()->flash('warning', 'Jumlah anggota melebihi batas paket. Silakan upgrade paket atau kurangi anggota.');
            }
        }

        return $next($request);
    }
}
