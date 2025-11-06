<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = ['name'];

    // relasi ke userCompanies
    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'roles_id');
    }

    // relasi ke userWorkspaces
    public function userWorkspaces()
    {
        return $this->hasMany(UserWorkspace::class, 'roles_id');
    }

  

}
