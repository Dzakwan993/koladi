<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false; // karena UUID
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'full_name',
        'email',
        'password',
        'google_id',
        'status_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    protected $hidden = ['password', 'remember_token'];

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'user_id');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id')
            ->withPivot('roles_id')
            ->withTimestamps();
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }
}
