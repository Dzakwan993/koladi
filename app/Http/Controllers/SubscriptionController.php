<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Addon;
use App\Models\Company;
use App\Models\UserCompany;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // ✅ TAMBAHKAN untuk upload file
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Configuration::setXenditKey(config('xendit.secret_key'));
    }

    // // Tampilkan halaman payment dengan info trial
    // public function index()
    // {
    //     // ✅ Ambil company_id dari session
    //     $companyId = session('active_company_id');

    //     // ✅ Jika tidak ada di session, coba ambil dari user_companies
    //     if (!$companyId) {
    //         $userCompany = UserCompany::where('user_id', auth()->id())->first();

    //         if (!$userCompany) {
    //             return redirect()->route('dashboard')
    //                 ->with('error', 'Anda belum terdaftar di perusahaan manapun.');
    //         }

    //         $companyId = $userCompany->company_id;
    //         session(['active_company_id' => $companyId]);
    //     }

    //     // ✅ Verifikasi user punya akses
    //     $hasAccess = UserCompany::where('user_id', auth()->id())
    //         ->where('company_id', $companyId)
    //         ->exists();

    //     if (!$hasAccess) {
    //         return redirect()->route('dashboard')
    //             ->with('error', 'Anda tidak memiliki akses ke perusahaan ini.');
    //     }

    //     // ✅ Load company dengan relasi
    //     $company = Company::with(['subscription.plan', 'users'])
    //         ->find($companyId);

    //     // ✅ Jika company tidak ditemukan
    //     if (!$company) {
    //         return redirect()->route('dashboard')
    //             ->with('error', 'Perusahaan tidak ditemukan.');
    //     }

    //     // ✅ Ambil SEMUA companies yang user punya akses
    //     $companies = Company::whereHas('users', function ($q) {
    //         $q->where('users.id', auth()->id());
    //     })
    //         ->with(['users', 'subscription.plan'])
    //         ->get();

    //     $trialDaysLeft = 0;
    //     $trialStatus = 'expired';

    //     if ($company->status === 'trial' && $company->trial_end) {
    //         $trialEnds = Carbon::parse($company->trial_end);
    //         $now = Carbon::now();

    //         if ($trialEnds->isFuture()) {
    //             $trialDaysLeft = $now->diffInDays($trialEnds);
    //             $trialStatus = 'active';
    //         }
    //     }

    //     $invoices = SubscriptionInvoice::whereHas('subscription', function ($q) use ($company) {
    //         $q->where('company_id', $company->id);
    //     })
    //         ->with('subscription.plan')
    //         ->latest()
    //         ->get();

    //     $hasActiveSubscription = $company->subscription &&
    //         $company->subscription->status === 'active' &&
    //         Carbon::parse($company->subscription->end_date)->isFuture();

    //     return view('pembayaran', compact(
    //         'company',
    //         'companies', // ✅ Kirim semua companies
    //         'invoices',
    //         'trialDaysLeft',
    //         'trialStatus',
    //         'hasActiveSubscription'
    //     ));
    // }

    // // API: Get plans untuk modal
    // public function getPlans()
    // {
    //     try {
    //         $plans = Plan::where('is_active', true)
    //             ->select('id', 'plan_name', 'price_monthly', 'base_user_limit')
    //             ->orderBy('price_monthly')
    //             ->get();

    //         $addon = Addon::where('is_active', true)
    //             ->select('id', 'addon_name', 'price_per_user')
    //             ->first();

    //         return response()->json([
    //             'plans' => $plans,
    //             'addon' => $addon
    //         ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
    //             ->header('Pragma', 'no-cache')
    //             ->header('Expires', '0');
    //     } catch (\Exception $e) {
    //         Log::error('Error getting plans:', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal memuat paket'
    //         ], 500);
    //     }
    // }


    public function index()
    {
        $companyId = session('active_company_id');

        if (!$companyId) {
            $userCompany = UserCompany::where('user_id', auth()->id())->first();
            if (!$userCompany) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda belum terdaftar di perusahaan manapun.');
            }
            $companyId = $userCompany->company_id;
            session(['active_company_id' => $companyId]);
        }

        $hasAccess = UserCompany::where('user_id', auth()->id())
            ->where('company_id', $companyId)
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke perusahaan ini.');
        }

        $userCompany = UserCompany::where('user_id', auth()->id())
            ->where('company_id', $companyId)
            ->where('status_active', true) // pastikan user masih aktif
            ->with('role') // eager load role
            ->first();

        $currentUserRole = $userCompany->role->name;

        $company = Company::with(['subscription.plan', 'users'])->find($companyId);

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'Perusahaan tidak ditemukan.');
        }

        $companies = Company::whereHas('users', function ($q) {
            $q->where('users.id', auth()->id());
        })->with(['users', 'subscription.plan'])->get();

        $trialDaysLeft = 0;
        $trialStatus = 'expired';

        if ($company->status === 'trial' && $company->trial_end) {
            $trialEnds = Carbon::parse($company->trial_end);
            if ($trialEnds->isFuture()) {
                $trialDaysLeft = $trialEnds->diffInDays(now());
                $trialStatus = 'active';
            }
        }

        $invoices = SubscriptionInvoice::whereHas('subscription', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->with('subscription.plan')->latest()->get();

        $hasActiveSubscription = $company->subscription &&
            $company->subscription->status === 'active' &&
            Carbon::parse($company->subscription->end_date)->isFuture();

        return view('pembayaran', compact(
            'company',
            'companies',
            'invoices',
            'trialDaysLeft',
            'trialStatus',
            'hasActiveSubscription',
            'currentUserRole' // 🔥 PENTING: Jangan lupa pass variable ini!
        ));
    }

    // API: Get plans
    public function getPlans()
    {
        try {
            $plans = Plan::where('is_active', true)
                ->select('id', 'plan_name', 'price_monthly', 'base_user_limit')
                ->orderBy('price_monthly')
                ->get();

            $addon = Addon::where('is_active', true)
                ->select('id', 'addon_name', 'price_per_user')
                ->first();

            return response()->json([
                'plans' => $plans,
                'addon' => $addon
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('Error getting plans:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat paket'
            ], 500);
        }
    }

    // 🔥 CREATE SUBSCRIPTION - MANUAL PAYMENT
    public function createSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'addon_user_count' => 'nullable|integer|min:0',
            'company_id' => 'required|exists:companies,id',
            'payment_method' => 'required|in:xendit,manual',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // 1. Ambil Data Utama
            // =========================
            $plan = Plan::findOrFail($request->plan_id);
            $addon = Addon::where('is_active', true)->first();
            $company = Company::findOrFail($request->company_id);

            $addonCount = $request->addon_user_count ?? 0;
            $activeUserCount = $company->active_users_count;
            $newLimit = $plan->base_user_limit + $addonCount;

            // =========================
            // 2. Validasi Akses User
            // =========================
            $hasAccess = UserCompany::where('user_id', auth()->id())
                ->where('company_id', $company->id)
                ->where('status_active', true)
                ->exists();

            if (!$hasAccess) {
                throw new \Exception('Anda tidak memiliki akses ke perusahaan ini');
            }

            // =========================
            // 3. Validasi Downgrade Paket
            // =========================
            $currentSubscription = $company->subscription;
            $isDowngrade = false;

            if ($currentSubscription && $currentSubscription->plan) {
                $currentPlan = $currentSubscription->plan;
                $isDowngrade = $plan->base_user_limit < $currentPlan->base_user_limit;
            }

            if ($isDowngrade && $activeUserCount > $newLimit) {
                $excess = $activeUserCount - $newLimit;

                throw new \Exception(json_encode([
                    'type' => 'downgrade_error',
                    'title' => 'Tidak Dapat Downgrade',
                    'message' => "User aktif saat ini: {$activeUserCount}\n" .
                        "Limit paket baru: {$newLimit}\n\n" .
                        "Silakan nonaktifkan {$excess} user terlebih dahulu sebelum downgrade paket.",
                    'excess' => $excess,
                ]));
            }

            // =========================
            // 4. Validasi Trial → Paid
            // =========================
            $isComingFromTrial =
                $company->status === 'trial' &&
                (!$currentSubscription || $currentSubscription->status === 'trial');

            if ($isComingFromTrial && $activeUserCount > $newLimit) {
                $excess = $activeUserCount - $newLimit;

                throw new \Exception(json_encode([
                    'type' => 'trial_to_paid_error',
                    'title' => 'Limit Paket Tidak Cukup',
                    'message' => "User aktif saat ini: {$activeUserCount}\n" .
                        "Limit paket baru: {$newLimit}\n\n" .
                        "Silakan nonaktifkan {$excess} user atau pilih paket dengan limit yang lebih besar.",
                    'excess' => $excess,
                ]));
            }

            // =========================
// 4B. Validasi Trial SUDAH EXPIRED
// =========================
            $isTrialExpired =
                !$company->isOnTrial() &&
                !$company->hasActiveSubscription();

            if ($isTrialExpired) {

                // 🔥 Wajib beli paket (tidak boleh bypass)
                if ($activeUserCount > $newLimit) {
                    $excess = $activeUserCount - $newLimit;

                    throw new \Exception(json_encode([
                        'type' => 'trial_expired_error',
                        'title' => 'Trial Anda Telah Berakhir',
                        'message' => "Masa trial perusahaan Anda telah berakhir.\n\n" .
                            "User aktif saat ini: {$activeUserCount}\n" .
                            "Limit paket yang dipilih: {$newLimit}\n\n" .
                            "Silakan nonaktifkan {$excess} user atau pilih paket dengan limit lebih besar untuk melanjutkan.",
                        'excess' => $excess,
                    ]));
                }
            }


            // =========================
            // 5. Hitung Biaya
            // =========================
            $addonPrice = $addonCount * ($addon->price_per_user ?? 0);
            $totalAmount = $plan->price_monthly + $addonPrice;

            // =========================
            // 6. Ambil / Buat Subscription
            // =========================
            $subscription = Subscription::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'status' => 'trial',
                    'total_user_limit' => 0,
                    'start_date' => now(),
                    'end_date' => now()->addDays(7),
                ]
            );


            // 6.5 Hapus invoice pending lama yang belum ada bukti bayar
            // =========================
            SubscriptionInvoice::where('subscription_id', $subscription->id)
                ->where('status', 'pending')
                ->whereNull('proof_of_payment')
                ->delete();

            // =========================
            // 7. Generate Invoice
            // =========================
            $orderId = 'INV-' . time() . '-' . rand(100, 999);

            $invoice = SubscriptionInvoice::create([
                'subscription_id' => $subscription->id,
                'external_id' => $orderId,
                'amount' => $totalAmount,
                'billing_month' => now()->format('Y-m'),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_details' => [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->plan_name,
                    'new_addon_count' => $addonCount,
                    'new_total_limit' => $newLimit,
                    'is_downgrade' => $isDowngrade,
                ],
            ]);

            Log::info('✅ Invoice created', [
                'invoice_id' => $invoice->id,
                'company_id' => $company->id,
                'plan' => $plan->plan_name,
                'is_downgrade' => $isDowngrade,
                'active_users' => $activeUserCount,
                'new_limit' => $newLimit,
            ]);

            // =========================
            // 8. Response Berdasarkan Metode Pembayaran
            // =========================
            if ($request->payment_method === 'manual') {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'payment_method' => 'manual',
                    'invoice_id' => $invoice->id,
                    'external_id' => $orderId,
                    'amount' => $totalAmount,
                    'is_downgrade' => $isDowngrade,
                    'message' => 'Invoice berhasil dibuat. Silakan upload bukti transfer.',
                ]);
            }

            if ($request->payment_method === 'xendit') {
                DB::commit();
                $xenditInvoice = $this->createXenditInvoice($invoice, $company, $plan);
                return response()->json([
                    'success' => true,
                    'payment_method' => 'xendit',
                    'invoice_url' => $xenditInvoice['invoice_url'],
                    'external_id' => $orderId,
                    'amount' => $totalAmount,
                ]);
            }

            throw new \Exception('Metode pembayaran tidak didukung saat ini.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Subscription creation error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleUserStatus(Request $request)
    {
        $request->validate([
            'user_company_id' => 'required|exists:user_companies,id',
            'status_active' => 'required|boolean'
        ]);

        try {
            $companyId = session('active_company_id');

            // Validasi SuperAdmin
            $currentUserCompany = UserCompany::where('user_id', auth()->id())
                ->where('company_id', $companyId)
                ->with('role')
                ->first();

            if (!$currentUserCompany || !in_array($currentUserCompany->role->name, ['SuperAdmin', 'Super Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya SuperAdmin yang dapat mengubah status user'
                ], 403);
            }

            $userCompany = UserCompany::findOrFail($request->user_company_id);

            // Tidak bisa nonaktifkan diri sendiri
            if ($userCompany->user_id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak dapat menonaktifkan akun sendiri'
                ], 403);
            }

            // 🔥 VALIDASI BARU: Jika mau AKTIFKAN user
            if ($request->status_active === true) {
                $company = Company::with('subscription')->findOrFail($companyId);

                // Hitung jumlah user yang SUDAH AKTIF saat ini
                $currentActiveCount = UserCompany::where('company_id', $companyId)
                    ->where('status_active', true)
                    ->count();

                // Ambil limit dari subscription
                $userLimit = $company->subscription->total_user_limit ?? 0;

                // 🔥 CEK: Apakah sudah mencapai batas?
                if ($currentActiveCount >= $userLimit) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak dapat mengaktifkan user. Batas maksimal ({$userLimit} user aktif) sudah tercapai. Silakan nonaktifkan user lain terlebih dahulu atau upgrade paket."
                    ], 400);
                }

                Log::info('✅ User activation allowed', [
                    'current_active' => $currentActiveCount,
                    'limit' => $userLimit,
                    'user_to_activate' => $userCompany->user->email
                ]);
            }

            DB::beginTransaction();

            // ✅ Update status di user_companies
            $userCompany->status_active = $request->status_active;
            $userCompany->save();

            // 🔥 JIKA NONAKTIFKAN: Hapus dari SEMUA workspace di company ini
            if ($request->status_active === false) {
                $removedWorkspaces = \App\Models\UserWorkspace::whereHas('workspace', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                    ->where('user_id', $userCompany->user_id)
                    ->get();

                // Log workspaces yang akan dihapus
                Log::info('🗑️ Removing user from workspaces', [
                    'user_id' => $userCompany->user_id,
                    'company_id' => $companyId,
                    'workspace_count' => $removedWorkspaces->count(),
                    'workspaces' => $removedWorkspaces->pluck('workspace_id')->toArray()
                ]);

                // Hapus dari semua workspace
                \App\Models\UserWorkspace::whereHas('workspace', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                    ->where('user_id', $userCompany->user_id)
                    ->delete();
            }

            DB::commit();

            $statusText = $request->status_active ? 'diaktifkan' : 'dinonaktifkan';
            $additionalMessage = !$request->status_active
                ? ' dan dihapus dari semua workspace'
                : '';

            Log::info("User status changed", [
                'user_id' => $userCompany->user_id,
                'company_id' => $userCompany->company_id,
                'status_active' => $request->status_active,
                'changed_by' => auth()->id(),
                'workspaces_removed' => !$request->status_active
            ]);

            return response()->json([
                'success' => true,
                'message' => "User berhasil {$statusText}{$additionalMessage}"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Toggle user status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUsersWithStatus($companyId)
    {
        try {
            $company = Company::findOrFail($companyId);

            // Validasi akses
            $hasAccess = UserCompany::where('user_id', auth()->id())
                ->where('company_id', $companyId)
                ->where('status_active', true)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses'
                ], 403);
            }

            $users = $company->userCompanies()
                ->with(['user', 'role'])
                ->get()
                ->map(function ($uc) {
                    return [
                        'user_company_id' => $uc->id,
                        'user_id' => $uc->user_id,
                        'full_name' => $uc->user->full_name,
                        'email' => $uc->user->email,
                        'avatar' => $uc->user->avatar,
                        'role_name' => $uc->role->name ?? 'Member',
                        'status_active' => $uc->status_active
                    ];
                })
                // Filter out current user (tidak bisa disable diri sendiri)
                ->filter(fn($u) => $u['user_id'] !== auth()->id())
                // Filter out AdminSistem
                ->filter(fn($u) => $u['role_name'] !== 'AdminSistem');

            return response()->json([
                'success' => true,
                'users' => $users->values()
            ]);
        } catch (\Exception $e) {
            Log::error('Get users status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }

    // 🔥 UPLOAD BUKTI TRANSFER
    // 🔥 UPLOAD BUKTI TRANSFER - UPDATED VERSION
    public function uploadProof(Request $request)
    {
        try {
            $request->validate([
                'invoice_id' => 'required|string',
                'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'payer_name' => 'required|string|max:255',
                'payer_bank' => 'required|string|max:100',
                'payer_account_number' => 'required|string|max:50'
            ], [
                'invoice_id.required' => 'Invoice ID tidak ditemukan',
                'proof_file.required' => 'File bukti transfer wajib diupload',
                'proof_file.image' => 'File harus berupa gambar',
                'proof_file.mimes' => 'Format file harus JPG, JPEG, atau PNG',
                'proof_file.max' => 'Ukuran file maksimal 2MB',
                'payer_name.required' => 'Nama pengirim wajib diisi',
                'payer_bank.required' => 'Bank pengirim wajib dipilih',
                'payer_account_number.required' => 'Nomor rekening wajib diisi'
            ]);

            DB::beginTransaction();

            // Cari invoice berdasarkan external_id
            $invoice = SubscriptionInvoice::where('external_id', $request->invoice_id)
                ->with('subscription.company')
                ->first();

            if (!$invoice) {
                throw new \Exception('Invoice tidak ditemukan');
            }

            // Validasi invoice
            if ($invoice->status === 'paid') {
                throw new \Exception('Invoice sudah dibayar');
            }

            if ($invoice->payment_method !== 'manual') {
                throw new \Exception('Invoice ini bukan pembayaran manual');
            }

            // Validasi akses user
            $hasAccess = UserCompany::where('user_id', auth()->id())
                ->where('company_id', $invoice->subscription->company_id)
                ->exists();

            if (!$hasAccess) {
                throw new \Exception('Anda tidak memiliki akses');
            }

            // Hapus bukti lama jika ada
            if ($invoice->proof_of_payment) {
                Storage::disk('public')->delete($invoice->proof_of_payment);
            }

            // Upload bukti baru
            $file = $request->file('proof_file');
            $filename = 'proof_' . $invoice->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('payment_proofs', $filename, 'public');

            // Update invoice dengan informasi pembayaran
            $invoice->update([
                'proof_of_payment' => $path,
                'payer_name' => $request->payer_name,
                'payer_bank' => $request->payer_bank,
                'payer_account_number' => $request->payer_account_number,
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.',
                'proof_url' => Storage::url($path)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload proof error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyManualPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:subscription_invoices,id',
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $invoice = SubscriptionInvoice::with(['subscription.company', 'subscription.plan'])
                ->findOrFail($request->invoice_id);

            if ($invoice->payment_method !== 'manual') {
                throw new \Exception('Bukan pembayaran manual');
            }

            if (!$invoice->proof_of_payment) {
                throw new \Exception('Belum ada bukti pembayaran');
            }

            if ($request->action === 'approve') {
                // 1. Update status invoice
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'admin_notes' => $request->admin_notes
                ]);

                // 2. 🔥 AMBIL DATA PAKET DARI payment_details
                $paymentDetails = $invoice->payment_details;
                $newPlanId = $paymentDetails['plan_id'] ?? null;
                $newAddonCount = $paymentDetails['new_addon_count'] ?? 0;
                $newTotalLimit = $paymentDetails['new_total_limit'] ?? 0;

                if (!$newPlanId) {
                    throw new \Exception('Data paket tidak ditemukan di invoice');
                }

                // 3. 🔥 UPDATE SUBSCRIPTION dengan data BARU (REPLACE, bukan ADD)
                $subscription = $invoice->subscription;

                $subscription->update([
                    'plan_id' => $newPlanId,                    // 🔥 UPDATE plan
                    'addons_user_count' => $newAddonCount,      // 🔥 UPDATE addon count
                    'total_user_limit' => $newTotalLimit,       // 🔥 REPLACE limit (bukan tambah!)
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth()
                ]);

                // 4. Update company status
                $subscription->company->update([
                    'status' => 'active',
                    'trial_end' => null  // Hapus trial jika ada
                ]);

                // 5. 🔥 LOG untuk debugging
                Log::info('✅ Payment approved - Subscription updated', [
                    'invoice_id' => $invoice->id,
                    'old_plan' => $invoice->subscription->plan->plan_name ?? 'N/A',
                    'new_plan_id' => $newPlanId,
                    'new_addon_count' => $newAddonCount,
                    'new_total_limit' => $newTotalLimit,
                    'company_id' => $subscription->company_id
                ]);

                $message = 'Pembayaran berhasil diverifikasi dan subscription diaktifkan';
            } else {
                // Reject pembayaran
                $invoice->update([
                    'status' => 'failed',
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'admin_notes' => $request->admin_notes ?? 'Pembayaran ditolak oleh admin'
                ]);

                Log::info('❌ Payment rejected', [
                    'invoice_id' => $invoice->id,
                    'reason' => $request->admin_notes
                ]);

                $message = 'Pembayaran ditolak';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verify payment error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ Tampilkan halaman Access Blocked
    public function showAccessBlocked()
    {
        $companyId = session('active_company_id');
        $company = null;

        if ($companyId) {
            $company = Company::find($companyId);
        }

        return view('access-blocked', compact('company'));
    }

    // Xendit Webhook Callback
    public function xenditCallback(Request $request)
    {
        try {
            // Verifikasi token dari Xendit
            $webhookToken = $request->header('x-callback-token');
            if ($webhookToken !== config('xendit.webhook_token')) {
                Log::warning('❌ Invalid Xendit webhook token');
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $externalId = $request->input('external_id');
            $status = $request->input('status');

            $invoice = SubscriptionInvoice::where('external_id', $externalId)
                ->with(['subscription.company', 'subscription.plan'])
                ->first();

            if (!$invoice) {
                Log::error('❌ Invoice not found', ['external_id' => $externalId]);
                return response()->json(['message' => 'Invoice not found'], 404);
            }

            Log::info('📥 Xendit webhook received', [
                'external_id' => $externalId,
                'status' => $status,
            ]);

            if ($status === 'PAID') {
                DB::beginTransaction();

                $paymentDetails = $invoice->payment_details;
                $newPlanId = $paymentDetails['plan_id'] ?? null;
                $newAddonCount = $paymentDetails['new_addon_count'] ?? 0;
                $newTotalLimit = $paymentDetails['new_total_limit'] ?? 0;

                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_details' => array_merge($paymentDetails, $request->all()),
                ]);

                $invoice->subscription->update([
                    'plan_id' => $newPlanId,
                    'addons_user_count' => $newAddonCount,
                    'total_user_limit' => $newTotalLimit,
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                ]);

                $invoice->subscription->company->update([
                    'status' => 'active',
                    'trial_end' => null,
                ]);

                DB::commit();
                Log::info('✅ Xendit payment success', ['external_id' => $externalId]);
            } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
                $invoice->update(['status' => 'expired']);
                Log::warning('⚠️ Xendit payment expired/failed', ['external_id' => $externalId]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Xendit callback error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    // Check trial status
    public function checkTrialStatus()
    {
        $companyId = session('active_company_id');
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        $isTrialActive = false;
        $daysLeft = 0;

        if ($company->status === 'trial' && $company->trial_end) {
            $trialEnds = Carbon::parse($company->trial_end);
            if ($trialEnds->isFuture()) {
                $isTrialActive = true;
                $daysLeft = Carbon::now()->diffInDays($trialEnds);
            }
        }

        return response()->json([
            'is_trial_active' => $isTrialActive,
            'days_left' => $daysLeft,
            'trial_ends_at' => $company->trial_end,
            'has_active_subscription' => $company->subscription && $company->subscription->status === 'active'
        ]);
    }

    public function cancelPending(Request $request)
    {
        try {
            SubscriptionInvoice::where('external_id', $request->external_id)
                ->where('status', 'pending')
                ->whereNull('proof_of_payment')
                ->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    private function createXenditInvoice($invoice, $company, $plan)
    {
        $apiInstance = new InvoiceApi();

        $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest([
            'external_id' => $invoice->external_id,
            'amount' => $invoice->amount,
            'description' => 'Koladi - ' . $plan->plan_name,
            'invoice_duration' => 86400, // 24 jam
            'currency' => 'IDR',
            'reminder_time' => 1,
            'success_redirect_url' => url('/pembayaran?status=success'),
            'failure_redirect_url' => url('/pembayaran?status=failed'),
            'items' => [
                [
                    'name' => $plan->plan_name,
                    'quantity' => 1,
                    'price' => $invoice->amount,
                ]
            ],
        ]);

        $result = $apiInstance->createInvoice($createInvoiceRequest);

        // Simpan invoice URL ke database
        $invoice->update([
            'payment_url' => $result['invoice_url'],
        ]);

        Log::info('✅ Xendit invoice created', [
            'external_id' => $invoice->external_id,
            'invoice_url' => $result['invoice_url'],
        ]);

        return $result;
    }
}
