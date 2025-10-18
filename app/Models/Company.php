<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = ['name', 'email', 'address', 'phone'];

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'company_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_companies', 'company_id', 'user_id')
            ->withPivot('roles_id')
            ->withTimestamps();
    }
}
