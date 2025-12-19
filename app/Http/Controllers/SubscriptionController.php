<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Midtrans\Snap;
use App\Models\Plan;
use Midtrans\Config;
use App\Models\Addon;
use App\Models\Company;
use App\Models\UserCompany;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // ✅ TAMBAHKAN untuk upload file

class SubscriptionController extends Controller
{
    public function __construct()
    {
        // Config::$serverKey = config('midtrans.server_key');
        // Config::$isProduction = config('midtrans.is_production');
        // Config::$isSanitized = config('midtrans.is_sanitized');
        // Config::$is3ds = config('midtrans.is_3ds');
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
            'hasActiveSubscription'
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
            'addon_user_count' => 'integer|min:0',
            'company_id' => 'required|exists:companies,id',
            'payment_method' => 'required|in:midtrans,manual'
        ]);

        DB::beginTransaction();
        try {
            $plan = Plan::findOrFail($request->plan_id);
            $addon = Addon::where('is_active', true)->first();
            $company = Company::findOrFail($request->company_id);

            // 1. Validasi akses user
            $hasAccess = UserCompany::where('user_id', auth()->id())
                ->where('company_id', $company->id)
                ->exists();

            if (!$hasAccess) {
                throw new \Exception('Anda tidak memiliki akses ke perusahaan ini');
            }

            // 2. Hitung total biaya
            $addonCount = $request->addon_user_count ?? 0;
            $totalUserLimit = $plan->base_user_limit + $addonCount;
            $addonPrice = $addonCount * ($addon->price_per_user ?? 0);
            $totalAmount = $plan->price_monthly + $addonPrice;

            // 3. Ambil record Subscription yang sudah ada (JANGAN UPDATE DATA DI SINI)
            // Jika belum ada sama sekali, baru kita buatkan defaultnya
            $subscription = Subscription::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'status' => 'trial',
                    'total_user_limit' => 5,
                    'start_date' => now(),
                    'end_date' => now()->addDays(7)
                ]
            );

            // 4. Generate Order ID
            $orderId = 'INV-' . time() . '-' . rand(100, 999);

            // 5. Buat Invoice dan SIMPAN RENCANA perubahan di payment_details
            // Setelah baris ini:
            $invoice = SubscriptionInvoice::create([
                'subscription_id' => $subscription->id,
                'external_id' => $orderId,
                'amount' => $totalAmount,
                'billing_month' => now()->format('Y-m'),
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_details' => [
                    'plan_id' => $plan->id,
                    'new_addon_count' => $addonCount,
                    'new_total_limit' => $totalUserLimit
                ]
            ]);
            Log::info('Invoice created', [
                'plan_id' => $plan->id,
                'plan_name' => $plan->plan_name, // 🔥 TAMBAHKAN
                'addon_count' => $addonCount,
                'total_limit' => $totalUserLimit
            ]);

            if ($request->payment_method === 'manual') {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'payment_method' => 'manual',
                    'invoice_id' => $invoice->id,
                    'external_id' => $orderId,
                    'amount' => $totalAmount,
                    'message' => 'Invoice berhasil dibuat. Silakan upload bukti transfer.'
                ]);
            }

            throw new \Exception('Metode pembayaran tidak didukung saat ini.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 🔥 UPLOAD BUKTI TRANSFER
    public function uploadProof(Request $request)
    {
        try {
            $request->validate([
                'invoice_id' => 'required|string',
                'proof_file' => 'required|image|mimes:jpeg,png,jpg|max:2048'
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

            // Update invoice
            $invoice->update([
                'proof_of_payment' => $path,
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
                'message' => 'Validasi gagal: ' . implode(', ', $e->validator->errors()->all())
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

        // TODO: Tambahkan middleware admin untuk route ini

        DB::beginTransaction();
        try {
            $invoice = SubscriptionInvoice::with('subscription.company')
                ->findOrFail($request->invoice_id);

            if ($invoice->payment_method !== 'manual') {
                throw new \Exception('Bukan pembayaran manual');
            }

            if (!$invoice->proof_of_payment) {
                throw new \Exception('Belum ada bukti pembayaran');
            }

            if ($request->action === 'approve') {
                // Approve pembayaran
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                    'admin_notes' => $request->admin_notes
                ]);

                // Aktifkan subscription
                $subscription = $invoice->subscription;
                $subscription->update([
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth()
                ]);

                // Update company status
                $subscription->company->update([
                    'status' => 'active'
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

                $message = 'Pembayaran ditolak';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verify payment error:', ['error' => $e->getMessage()]);

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

    // // Webhook callback dari Midtrans
    // public function callback(Request $request)
    // {
    //     try {
    //         Config::$serverKey = config('midtrans.server_key');
    //         Config::$isProduction = config('midtrans.is_production');
    //         Config::$isSanitized = config('midtrans.is_sanitized');
    //         Config::$is3ds = config('midtrans.is_3ds');

    //         $serverKey = config('midtrans.server_key');
    //         $hashed = hash(
    //             "sha512",
    //             $request->order_id . $request->status_code . $request->gross_amount . $serverKey
    //         );

    //         if ($hashed !== $request->signature_key) {
    //             Log::warning('Invalid Midtrans signature', ['order_id' => $request->order_id]);
    //             return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
    //         }

    //         $invoice = SubscriptionInvoice::where('external_id', $request->order_id)->first();

    //         if (!$invoice) {
    //             Log::error('Invoice not found', ['order_id' => $request->order_id]);
    //             return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
    //         }

    //         Log::info('Midtrans Callback Received', [
    //             'order_id' => $request->order_id,
    //             'transaction_status' => $request->transaction_status,
    //             'payment_type' => $request->payment_type,
    //         ]);

    //         $transactionStatus = $request->transaction_status;

    //         if (in_array($transactionStatus, ['settlement', 'capture'])) {
    //             $invoice->update([
    //                 'status' => 'paid',
    //                 'paid_at' => now(),
    //                 'payment_details' => $request->all()
    //             ]);

    //             $subscription = $invoice->subscription;
    //             $subscription->update([
    //                 'status' => 'active',
    //                 'start_date' => now(),
    //                 'end_date' => now()->addMonth()
    //             ]);

    //             $subscription->company->update([
    //                 'status' => 'active',
    //                 'is_on_trial' => false,
    //                 'trial_end' => null
    //             ]);

    //             Log::info('Payment successful', ['order_id' => $request->order_id]);
    //         } elseif ($transactionStatus === 'pending') {
    //             $invoice->update([
    //                 'status' => 'pending',
    //                 'payment_details' => $request->all()
    //             ]);

    //             Log::info('Payment pending', ['order_id' => $request->order_id]);
    //         } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
    //             $invoice->update([
    //                 'status' => 'expired',
    //                 'payment_details' => $request->all()
    //             ]);

    //             Log::warning('Payment failed/expired', [
    //                 'order_id' => $request->order_id,
    //                 'status' => $transactionStatus
    //             ]);
    //         }

    //         return response()->json(['success' => true]);
    //     } catch (\Exception $e) {
    //         Log::error('Midtrans callback error', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Internal server error'
    //         ], 500);
    //     }
    // }

    // Midtrans Callback (keep untuk nanti)
    public function callback(Request $request)
    {
        // Comment dulu, akan digunakan nanti
        return response()->json(['success' => true, 'message' => 'Callback diterima']);
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
}
