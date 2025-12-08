<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Addon;
use App\Models\Subscription;
use App\Models\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Tampilkan dashboard admin sistem
     */
    public function dashboard()
    {
        try {
            // 🔥 SOLUSI SEDERHANA: Langsung ambil companies tanpa map
            $companies = Company::whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('Companies fetched: ' . $companies->count());
            
            // Tambahkan computed properties ke setiap company
            foreach ($companies as $company) {
                // Hitung member count
                $company->member_count = DB::table('user_companies')
                    ->where('company_id', $company->id)
                    ->whereNull('deleted_at')
                    ->count();
                
                // Ambil subscription
                $subscription = Subscription::where('company_id', $company->id)
                    ->whereNull('deleted_at')
                    ->with('plan')
                    ->first();
                
                // Set package type
                $company->package_type = 'Trial';
                if ($subscription && $subscription->plan) {
                    $company->package_type = $subscription->plan->plan_name;
                }
                
                // Set addons
                $company->addons_user = $subscription ? $subscription->addons_user_count : 0;
                $company->addons_storage = 0;
            }

            Log::info('Companies processed: ' . $companies->count());

            // Hitung statistik total
            $totalCompanies = Company::whereNull('deleted_at')->count();
            $totalMembers = UserCompany::whereNull('deleted_at')->count();
            $activeCompanies = Company::whereNull('deleted_at')
                ->where('status', 'active')
                ->count();
            $trialCompanies = Company::whereNull('deleted_at')
                ->where('status', 'trial')
                ->count();

            // Ambil data paket dan addon
$plans = Plan::where('is_active', true)->get();

// Urutkan manual: Basic → Standard → Business
$order = ['Paket Basic', 'Paket Standard', 'Paket Business'];
$plans = $plans->sortBy(function ($plan) use ($order) {
    return array_search($plan->plan_name, $order);
})->values(); // values() supaya index 0,1,2
            $addons = Addon::where('is_active', true)->get();

            return view('dashboard_admin', [
    'allCompanies' => $companies, // gunakan nama baru
                'totalCompanies' => $totalCompanies,
                'totalMembers' => $totalMembers,
                'activeCompanies' => $activeCompanies,
                'trialCompanies' => $trialCompanies,
                'plans' => $plans,
                'addons' => $addons
            ]);

        } catch (\Exception $e) {
            Log::error('Admin Dashboard Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('dashboard_admin', [
                'companies' => collect([]),
                'totalCompanies' => 0,
                'totalMembers' => 0,
                'activeCompanies' => 0,
                'trialCompanies' => 0,
                'plans' => collect([]),
                'addons' => collect([])
            ])->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Tampilkan detail perusahaan
     */
    public function showCompany($id)
    {
        $company = Company::with(['userCompanies.user', 'userCompanies.role'])
            ->findOrFail($id);

        return view('admin.company_detail', compact('company'));
    }

    /**
     * Suspend/aktifkan perusahaan
     */
    public function toggleCompanyStatus($id)
    {
        $company = Company::findOrFail($id);
        
        // Toggle status
        $newStatus = $company->status === 'active' ? 'suspended' : 'active';
        $company->update(['status' => $newStatus]);
        
        return back()->with('success', 'Status perusahaan berhasil diubah');
    }
}