<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class UserCompany extends Model
{
    use HasFactory;

    protected $table = 'user_companies';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'user_id', 
        'company_id', 
        'roles_id',
        'status_active' // 🔥 TAMBAHAN BARU
    ];

    protected $casts = [
        'status_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
            
            // Default status_active = true
            if (is_null($model->status_active)) {
                $model->status_active = true;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }

    // 🔥 Helper: Cek apakah user aktif di company ini
    public function isActive(): bool
    {
        return $this->status_active === true;
    }

    // 🔥 Scope: Hanya user aktif
    public function scopeActive($query)
    {
        return $query->where('status_active', true);
    }

    // 🔥 Scope: User tidak aktif
    public function scopeInactive($query)
    {
        return $query->where('status_active', false);
    }
}