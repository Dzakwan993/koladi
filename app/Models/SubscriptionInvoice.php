<?php

namespace App\Models;

use App\Models\User; // ✅ TAMBAHKAN INI
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_id',
        'external_id',
        'payment_url',
        'amount',
        'billing_month',
        'status',
        'payment_method',        // 🔥 Baru
        'proof_of_payment',      // 🔥 Baru
        'admin_notes',           // 🔥 Baru
        'verified_at',           // 🔥 Baru
        'verified_by',           // 🔥 Baru
        'paid_at',
        'payment_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',  // 🔥 Baru
        'payment_details' => 'array'
    ];

    // Relationships
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Helper: Format amount ke rupiah
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format((float)$this->amount, 0, ',', '.');
    }

    // 🔥 Helper: Cek apakah invoice expired (3 hari)
    public function isExpired()
    {
        if ($this->status === 'paid') return false;
        return $this->created_at->addDays(3) < now();
    }

    // 🔥 Helper: Cek apakah manual payment
    public function isManualPayment()
    {
        return $this->payment_method === 'manual';
    }

    // 🔥 Helper: Cek apakah sudah diverifikasi
    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    // Scopes untuk query umum
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeManualPayment($query)
    {
        return $query->where('payment_method', 'manual');
    }

    public function scopeAwaitingVerification($query)
    {
        return $query->where('status', 'pending')
            ->where('payment_method', 'manual')
            ->whereNotNull('proof_of_payment');
    }

    // 🔥 Helper: Status badge untuk display
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'paid' => '<span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">✅ Lunas</span>',
            'pending' => '<span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">⏳ Menunggu</span>',
            'expired' => '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">❌ Kadaluarsa</span>',
            'failed' => '<span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">❌ Ditolak</span>', // 🔥 Pastikan ini ada
        ];

        return $badges[$this->status] ?? $badges['pending'];
    }
    // 🔥 TAMBAHKAN 3 METHOD INI DI BARIS PALING BAWAH

    /**
     * Ambil paket yang dibeli (bukan paket aktif saat ini)
     */
    public function getPurchasedPlanAttribute()
    {
        if (isset($this->payment_details['plan_id'])) {
            $plan = \App\Models\Plan::find($this->payment_details['plan_id']);
            if ($plan) {
                return $plan;
            }
        }

        // Fallback ke paket aktif jika tidak ada di payment_details
        return $this->subscription->plan ?? null;
    }

    /**
     * Nama paket yang dibeli (tanpa prefix "Paket")
     */
    public function getPurchasedPlanNameAttribute()
    {
        $plan = $this->purchased_plan;
        return $plan ? str_replace('Paket ', '', $plan->plan_name) : '-';
    }

    /**
     * Jumlah addon yang dibeli
     */
    public function getPurchasedAddonCountAttribute()
    {
        return $this->payment_details['new_addon_count'] ?? 0;
    }
}
