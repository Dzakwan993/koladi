<?php

namespace App\Models;

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
        'paid_at',
        'payment_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_details' => 'array'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Helper: Format amount ke rupiah
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format((float)$this->amount, 0, ',', '.');
    }

    // Helper: Cek apakah invoice expired (3 hari)
    public function isExpired()
    {
        if ($this->status === 'paid') return false;
        return $this->created_at->addDays(3) < now();
    }
}
