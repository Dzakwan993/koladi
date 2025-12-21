<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasUuids;

    protected $fillable = [
        'plan_name',
        'price_monthly',
        'base_user_limit',
        'description',
        'is_active'
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // Helper untuk format harga
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format((float) $this->price_monthly, 0, ',', '.');
    }
    public function getDisplayNameAttribute()
    {
        return str_replace('Paket ', '', $this->plan_name);
    }
}
