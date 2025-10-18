<?php

namespace App\View\Composers;

use App\Models\Company;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CompanyComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Ambil semua perusahaan user
            $companies = $user->companies;

            // ✅ DEBUGGING: Log jumlah perusahaan
            Log::info('Total perusahaan user: ' . $companies->count());

            // Ambil perusahaan aktif
            $activeCompany = session('active_company_id')
                ? Company::find(session('active_company_id'))
                : $companies->first();

            // ✅ DEBUGGING: Log perusahaan aktif
            Log::info('Active Company: ' . ($activeCompany ? $activeCompany->name : 'Tidak ada'));

            // Jika ada perusahaan aktif, simpan di session
            if ($activeCompany) {
                session(['active_company_id' => $activeCompany->id]);
            }

            $view->with([
                'companies' => $companies,
                'activeCompany' => $activeCompany
            ]);
        } else {
            $view->with([
                'companies' => collect([]),
                'activeCompany' => null
            ]);
        }
    }
}
