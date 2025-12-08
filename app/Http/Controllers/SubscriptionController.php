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

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    // Tampilkan halaman payment dengan info trial
    public function index()
    {
        // ✅ Ambil company_id dari session
        $companyId = session('active_company_id');

        // ✅ Jika tidak ada di session, coba ambil dari user_companies
        if (!$companyId) {
            $userCompany = UserCompany::where('user_id', auth()->id())->first();

            if (!$userCompany) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda belum terdaftar di perusahaan manapun.');
            }

            $companyId = $userCompany->company_id;
            session(['active_company_id' => $companyId]);
        }

        // ✅ Verifikasi user punya akses
        $hasAccess = UserCompany::where('user_id', auth()->id())
            ->where('company_id', $companyId)
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke perusahaan ini.');
        }

        // ✅ Load company dengan relasi
        $company = Company::with(['subscription.plan', 'users'])
            ->find($companyId);

        // ✅ Jika company tidak ditemukan
        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'Perusahaan tidak ditemukan.');
        }

        // ✅ Ambil SEMUA companies yang user punya akses
        $companies = Company::whereHas('users', function ($q) {
            $q->where('users.id', auth()->id());
        })
            ->with(['users', 'subscription.plan'])
            ->get();

        $trialDaysLeft = 0;
        $trialStatus = 'expired';

        if ($company->status === 'trial' && $company->trial_end) {
            $trialEnds = Carbon::parse($company->trial_end);
            $now = Carbon::now();

            if ($trialEnds->isFuture()) {
                $trialDaysLeft = $now->diffInDays($trialEnds);
                $trialStatus = 'active';
            }
        }

        $invoices = SubscriptionInvoice::whereHas('subscription', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })
            ->with('subscription.plan')
            ->latest()
            ->get();

        $hasActiveSubscription = $company->subscription &&
            $company->subscription->status === 'active' &&
            Carbon::parse($company->subscription->end_date)->isFuture();

        return view('pembayaran', compact(
            'company',
            'companies', // ✅ Kirim semua companies
            'invoices',
            'trialDaysLeft',
            'trialStatus',
            'hasActiveSubscription'
        ));
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

    // API: Get plans untuk modal
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

    // Proses pilih paket & generate Midtrans payment
    public function createSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'addon_user_count' => 'integer|min:0',
            'company_id' => 'required|exists:companies,id'
        ]);

        DB::beginTransaction();
        try {
            $plan = Plan::findOrFail($request->plan_id);
            $addon = Addon::where('is_active', true)->first();
            $company = Company::findOrFail($request->company_id);

            // ✅ Validasi akses user
            $hasAccess = UserCompany::where('user_id', auth()->id())
                ->where('company_id', $company->id)
                ->exists();

            if (!$hasAccess) {
                throw new \Exception('Anda tidak memiliki akses ke perusahaan ini');
            }

            // Hitung total
            $addonCount = $request->addon_user_count ?? 0;
            $totalUserLimit = $plan->base_user_limit + $addonCount;
            $addonPrice = $addonCount * ($addon->price_per_user ?? 0);
            $totalAmount = $plan->price_monthly + $addonPrice;

            // Create/Update Subscription
            $subscription = Subscription::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'plan_id' => $plan->id,
                    'addons_user_count' => $addonCount,
                    'total_user_limit' => $totalUserLimit,
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'status' => 'pending'
                ]
            );

            // Create Invoice
            $invoice = SubscriptionInvoice::create([
                'subscription_id' => $subscription->id,
                'amount' => $totalAmount,
                'billing_month' => now()->format('Y-m'),
                'status' => 'pending'
            ]);

            $orderId = 'INV' . time() . rand(100, 999);

            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalAmount,
            ];

            $itemDetails = [
                [
                    'id' => 'plan-' . $plan->id,
                    'price' => (int) $plan->price_monthly,
                    'quantity' => 1,
                    'name' => $plan->plan_name
                ]
            ];

            if ($addonCount > 0 && $addon) {
                $itemDetails[] = [
                    'id' => 'addon-' . $addon->id,
                    'price' => (int) $addon->price_per_user,
                    'quantity' => $addonCount,
                    'name' => 'Tambahan User (x' . $addonCount . ')'
                ];
            }

            $customerDetails = [
                'first_name' => $company->name,
                'email' => $company->email ?? auth()->user()->email,
            ];

            $params = [
                'transaction_details' => $transactionDetails,
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Update invoice
            $invoice->update([
                'external_id' => $orderId,
                'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'payment_url' => $invoice->payment_url,
                'invoice_id' => $invoice->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription creation error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Webhook callback dari Midtrans
    public function callback(Request $request)
    {
        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = config('midtrans.is_sanitized');
            Config::$is3ds = config('midtrans.is_3ds');

            $serverKey = config('midtrans.server_key');
            $hashed = hash(
                "sha512",
                $request->order_id . $request->status_code . $request->gross_amount . $serverKey
            );

            if ($hashed !== $request->signature_key) {
                Log::warning('Invalid Midtrans signature', ['order_id' => $request->order_id]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
            }

            $invoice = SubscriptionInvoice::where('external_id', $request->order_id)->first();

            if (!$invoice) {
                Log::error('Invoice not found', ['order_id' => $request->order_id]);
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            Log::info('Midtrans Callback Received', [
                'order_id' => $request->order_id,
                'transaction_status' => $request->transaction_status,
                'payment_type' => $request->payment_type,
            ]);

            $transactionStatus = $request->transaction_status;

            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_details' => $request->all()
                ]);

                $subscription = $invoice->subscription;
                $subscription->update([
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addMonth()
                ]);

                $subscription->company->update([
                    'status' => 'active',
                    'is_on_trial' => false,
                    'trial_end' => null
                ]);

                Log::info('Payment successful', ['order_id' => $request->order_id]);
            } elseif ($transactionStatus === 'pending') {
                $invoice->update([
                    'status' => 'pending',
                    'payment_details' => $request->all()
                ]);

                Log::info('Payment pending', ['order_id' => $request->order_id]);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $invoice->update([
                    'status' => 'expired',
                    'payment_details' => $request->all()
                ]);

                Log::warning('Payment failed/expired', [
                    'order_id' => $request->order_id,
                    'status' => $transactionStatus
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Midtrans callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    // Cek status trial company
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
