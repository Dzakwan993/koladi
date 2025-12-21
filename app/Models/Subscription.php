<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'plan_id',
        'addons_user_count',
        'total_user_limit',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'addons_user_count' => 'integer',
        'total_user_limit' => 'integer'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    // Helper: Cek apakah subscription masih aktif
    public function isActive()
    {
        return $this->status === 'active' &&
            Carbon::parse($this->end_date)->isFuture();
    }

    // Helper: Cek berapa hari tersisa
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) return 0;

        $endDate = Carbon::parse($this->end_date);
        $now = Carbon::now();

        if ($endDate->isPast()) return 0;

        return $now->diffInDays($endDate, false);
    }

    // 🔥 Helper: Hitung total harga per bulan (plan + addon)
    public function calculateMonthlyPrice()
    {
        if (!$this->plan) return 0;

        $planPrice = (float) $this->plan->price_monthly;

        // Ambil harga addon dari tabel addons
        $addon = Addon::where('is_active', true)->first();
        $addonPricePerUser = $addon ? (float) $addon->price_per_user : 0;

        $addonPrice = $this->addons_user_count * $addonPricePerUser;

        return $planPrice + $addonPrice;
    }

    // 🔥 Helper: Format harga
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->calculateMonthlyPrice(), 0, ',', '.');
    }

    // Scope: Hanya subscription aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>', now());
    }
}
