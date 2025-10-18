<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UserCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_companies';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'user_id', 'company_id', 'roles_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // ✅ Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ✅ Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // ✅ Relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }
}
