<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use HasFactory;

    protected $table = 'company_user';

    protected $fillable = [
        'id',
        'company_id',
        'user_id',
        'role_id',
    ];

    // Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
