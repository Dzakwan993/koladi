<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'nama_role',
    ];

    /**
     * Relasi ke User (jika user punya kolom id_role langsung)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'id_role');
    }

    /**
     * Relasi ke pivot company_user
     */
    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class, 'role_id');
    }

}
