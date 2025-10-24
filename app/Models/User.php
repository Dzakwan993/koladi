<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'full_name',
        'email',
        'password',
        'google_id',
        'status_active',
        'avatar', // Tambahkan ini jika kolom avatar sudah ada
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status_active' => 'boolean',
    ];

    // Accessor untuk avatar - jika kolom avatar belum ada
    public function getAvatarAttribute($value)
    {
        // Jika ada value dari database, gunakan itu
        if ($value) {
            return $value;
        }

        // Jika tidak ada, generate dari ui-avatars.com
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=random&color=fff';
    }

    // Atau bisa juga buat method terpisah
    public function getAvatarUrl()
    {
        if (!empty($this->attributes['avatar'])) {
            return $this->attributes['avatar'];
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=random&color=fff';
    }

    // Relasi ke companies
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id')
            ->withPivot('roles_id')
            ->withTimestamps();
    }

    // Relasi ke user_companies
    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'user_id');
    }


    public function getRoleName($companyId)
    {
        $userCompany = $this->userCompanies->where('company_id', $companyId)->first();
        return $userCompany && $userCompany->role ? $userCompany->role->name : null;
    }
}
