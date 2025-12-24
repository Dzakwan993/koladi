<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Addon;
use App\Models\Company;
use App\Models\UserCompany;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Exports\CompaniesExport;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
                    // 🔥 Hilangkan prefix "Paket " untuk display
                    $company->package_type = str_replace('Paket ', '', $subscription->plan->plan_name);
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

            // 1. Ambil invoice yang menunggu verifikasi (Pending)
            $pendingInvoices = SubscriptionInvoice::where('payment_method', 'manual')
                ->whereNotNull('proof_of_payment')
                ->where('status', 'pending')
                ->with(['subscription.company', 'subscription.plan'])
                ->latest()
                ->get();

            // 2. Ambil Riwayat Pembayaran (Paid / Failed)
            $historyInvoices = SubscriptionInvoice::whereIn('status', ['paid', 'failed'])
                ->with(['subscription.company', 'subscription.plan', 'verifiedBy'])
                ->latest()
                ->paginate(10); // Gunakan paginate agar responsif jika data banyak
            $plans = Plan::where('is_active', true)->get();

            // Urutkan manual: Basic → Standard → Business
            $order = ['Paket Basic', 'Paket Standard', 'Paket Business'];
            $plans = $plans->sortBy(function ($plan) use ($order) {
                return array_search($plan->plan_name, $order);
            })->values(); // values() supaya index 0,1,2
            $addons = Addon::where('is_active', true)->get();

            $feedbacks = \App\Models\Feedback::latest()->paginate(10);

            return view('dashboard_admin', [
                'allCompanies' => $companies,
                'totalCompanies' => $totalCompanies,
                'totalMembers' => $totalMembers,
                'activeCompanies' => $activeCompanies,
                'trialCompanies' => $trialCompanies,
                'plans' => $plans,
                'addons' => $addons,
                'pendingInvoices' => $pendingInvoices,
                'historyInvoices' => $historyInvoices,
                'feedbacks' => $feedbacks // Tambahkan ini
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
                'pendingInvoices' => collect([]),
                'historyInvoices' => collect([]),
                'plans' => collect([]),
                'addons' => collect([]),
                'feedbacks' => collect([]) // Tambahkan ini
            ])->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    // Tambahkan method baru untuk handle verifikasi
    public function verifyPayment(Request $request, $id)
{
    try {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();

        $invoice = SubscriptionInvoice::with('subscription.company')->findOrFail($id);
        $subscription = $invoice->subscription;
        $company = $subscription->company;

        if ($request->action === 'approve') {
            // ✅ UPDATE STATUS INVOICE
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'verified_at' => now(),
                'verified_by' => auth()->id(),
                'admin_notes' => $request->admin_notes ?? 'Disetujui oleh admin'
            ]);

            // ✅ AMBIL DATA DARI payment_details
            $details = $invoice->payment_details;
            $newPlan = Plan::find($details['plan_id']);
            $newAddonCount = $details['new_addon_count'] ?? 0;
            $isDowngrade = $details['is_downgrade'] ?? false;

            if (!$newPlan) {
                throw new \Exception('Plan tidak ditemukan');
            }

            // 🔥 LOGIKA BARU: REPLACE, BUKAN TAMBAH
            $newTotalLimit = $newPlan->base_user_limit + $newAddonCount;

            // ✅ CEK: Apakah subscription masih aktif?
            $isCurrentlyActive = $subscription->status === 'active' 
                && $subscription->end_date 
                && Carbon::parse($subscription->end_date)->isFuture();

            if ($isCurrentlyActive) {
                // 🔥 SUBSCRIPTION MASIH AKTIF: Extend end_date
                $currentEndDate = Carbon::parse($subscription->end_date);
                $newEndDate = $currentEndDate->addMonth();
            } else {
                // 🔥 SUBSCRIPTION EXPIRED/TRIAL: Start dari sekarang
                $newEndDate = now()->addMonth();
            }

            // ✅ UPDATE SUBSCRIPTION (REPLACE VALUES)
            $subscription->update([
                'status' => 'active',
                'plan_id' => $newPlan->id,
                'addons_user_count' => $newAddonCount, // 🔥 REPLACE (bukan tambah)
                'total_user_limit' => $newTotalLimit,  // 🔥 REPLACE (bukan tambah)
                'start_date' => $subscription->start_date ?? now(),
                'end_date' => $newEndDate,
            ]);

            // ✅ UPDATE COMPANY STATUS
            $company->update(['status' => 'active']);

            // 🔥 LOGGING UNTUK DEBUG
            Log::info('✅ Payment Verified & Subscription Updated', [
                'invoice_id' => $invoice->id,
                'company_id' => $company->id,
                'company_name' => $company->name,
                'plan' => $newPlan->plan_name,
                'base_limit' => $newPlan->base_user_limit,
                'addon_count' => $newAddonCount,
                'total_limit' => $newTotalLimit,
                'is_downgrade' => $isDowngrade,
                'active_users' => $company->active_users_count,
                'end_date' => $newEndDate->format('Y-m-d H:i:s')
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Pembayaran disetujui! Paket {$newPlan->plan_name} diaktifkan dengan limit {$newTotalLimit} user."
            ]);

        } else {
            // ✅ LOGIKA REJECT (tidak berubah)
            $invoice->update([
                'status' => 'failed',
                'admin_notes' => $request->admin_notes ?? 'Pembayaran ditolak.',
                'verified_at' => now(),
                'verified_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Pembayaran ditolak."
            ]);
        }

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('❌ Verify Payment Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false, 
            'message' => $e->getMessage()
        ], 500);
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

    /**
     * Update paket berlangganan
     */
    public function updatePlan(Request $request, $id)
    {
        try {
            $request->validate([
                'plan_name' => 'required|string|max:255',
                'price_monthly' => 'required|numeric|min:0',
                'base_user_limit' => 'required|integer|min:1',
            ]);

            $plan = Plan::findOrFail($id);

            $plan->update([
                'plan_name' => $request->plan_name,
                'price_monthly' => $request->price_monthly,
                'base_user_limit' => $request->base_user_limit,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil diperbarui',
                'data' => $plan
            ]);
        } catch (\Exception $e) {
            Log::error('Update Plan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui paket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update add-ons
     */
    public function updateAddon(Request $request, $id)
    {
        try {
            $request->validate([
                'price_per_user' => 'required|numeric|min:0',
            ]);

            $addon = Addon::findOrFail($id);

            $addon->update([
                'price_per_user' => $request->price_per_user,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Add-ons berhasil diperbarui',
                'data' => $addon
            ]);
        } catch (\Exception $e) {
            Log::error('Update Addon Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui add-ons: ' . $e->getMessage()
            ], 500);
        }
    }
    public function exportCompanies()
    {
        try {
            // Generate nama file dengan timestamp
            $fileName = 'Daftar_Perusahaan_' . Carbon::now()->format('d-M-Y_His') . '.xlsx';

            // Export ke Excel
            return Excel::download(new CompaniesExport, $fileName);
        } catch (\Exception $e) {
            Log::error('Export Companies Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()->with('error', 'Gagal export data perusahaan');
        }
    }

    public function deleteFeedback($id)
    {
        try {
            $feedback = \App\Models\Feedback::findOrFail($id);
            $feedback->delete();

            return response()->json([
                'success' => true,
                'message' => 'Feedback berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus feedback: ' . $e->getMessage()
            ], 500);
        }
    }
}
