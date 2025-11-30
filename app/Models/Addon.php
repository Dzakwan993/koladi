<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasUuids;

    protected $fillable = [
        'addon_name',
        'price_per_user',
        'description',
        'is_active'
    ];

    protected $casts = [
        'price_per_user' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Helper untuk format harga
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format((float) $this->price_per_user, 0, ',', '.');
    }

    // Helper untuk hitung total harga addon
    public function calculatePrice($quantity)
    {
        return $this->price_per_user * $quantity;
    }

    // Scope untuk addon aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
