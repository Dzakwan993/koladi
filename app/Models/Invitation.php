<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    // ✅ PostgreSQL: gunakan lowercase
    protected $table = 'invitations';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'email_target',
        'token',
        'status',
        'invited_by',
        'company_id',
        'expired_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    public static function generateToken()
    {
        return Str::random(64);
    }

    // Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi ke User yang mengundang
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
