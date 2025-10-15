<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'email',
        'address',
        'phone'
    ];

    // ✅ PENTING: Set primary key ke UUID
    public $incrementing = false;
    protected $keyType = 'string';

    // ✅ Auto-generate UUID saat creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // ✅ Relasi ke User (many-to-many)
    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user', 'company_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps();
    }
}
