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
        'avatar',
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

    // Accessor untuk avatar
    public function getAvatarAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=random&color=fff';
    }

    public function getAvatarUrl()
    {
        if (!empty($this->attributes['avatar'])) {
            return $this->attributes['avatar'];
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=random&color=fff';
    }

    // ===== RELASI COMPANY =====
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies', 'user_id', 'company_id')
            ->withPivot('roles_id')
            ->withTimestamps();
    }

    // public function getAvatarUrlAttribute()
    // {
    //     return $this->avatar
    //         ? asset('storage/' . $this->avatar) // path avatar di storage
    //         : asset('images/dk.jpg');          // default jika tidak ada
    // }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'user_id');
    }

    // ===== RELASI WORKSPACE =====
    public function userWorkspaces()
    {
        return $this->hasMany(UserWorkspace::class, 'user_id');
    }

    /**
     * ✅ Relationship untuk mendapatkan workspaces yang diikuti user
     * Relationship many-to-many melalui tabel user_workspaces
     */
    public function workspaces()
    {
        return $this->belongsToMany(
            Workspace::class,
            'user_workspaces',
            'user_id',
            'workspace_id'
        )
            ->withPivot('roles_id', 'status_active', 'join_date')
            ->wherePivot('status_active', true)
            ->withTimestamps();
    }

    /**
     * ✅ Mendapatkan semua workspaces (termasuk yang inactive)
     */
    public function allWorkspaces()
    {
        return $this->belongsToMany(
            Workspace::class,
            'user_workspaces',
            'user_id',
            'workspace_id'
        )
            ->withPivot('roles_id', 'status_active', 'join_date')
            ->withTimestamps();
    }

    /**
     * ✅ Relationship untuk workspaces yang dimiliki user (sebagai creator)
     */
    public function ownedWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'user_id');
    }

    // ===== HELPER METHODS COMPANY =====
    public function getRoleName($companyId)
    {
        $userCompany = $this->userCompanies->where('company_id', $companyId)->first();
        return $userCompany && $userCompany->role ? $userCompany->role->name : null;
    }

    public function hasCompanyRole($companyId, $roles = [])
    {
        $roleName = $this->getRoleName($companyId);
        return in_array($roleName, (array) $roles);
    }

    public function hasRoleInCompany(array $roleNames, $companyId)
    {
        return $this->userCompanies()
            ->where('company_id', $companyId)
            ->whereHas('role', function ($q) use ($roleNames) {
                $q->whereIn('name', $roleNames);
            })
            ->exists();
    }

    public function canManageWorkspaceRoles($companyId)
    {
        $allowed = ['Super Admin', 'SuperAdmin', 'Admin'];
        return $this->hasCompanyRole($companyId, $allowed);
    }

    // ===== HELPER METHODS WORKSPACE =====

    /**
     * ✅ Cek apakah user adalah member dari workspace tertentu
     */
    public function isMemberOf($workspaceId)
    {
        return $this->workspaces()->where('workspace_id', $workspaceId)->exists();
    }

    /**
     * ✅ Cek apakah user adalah admin/manager di company tertentu
     */
    public function isCompanyAdmin($companyId)
    {
        $userCompany = $this->userCompanies()
            ->where('company_id', $companyId)
            ->with('role')
            ->first();

        $roleName = $userCompany?->role?->name;

        return in_array($roleName, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']);
    }

    /**
     * ✅ Get role user di workspace tertentu
     */
    public function getWorkspaceRole($workspaceId)
    {
        $userWorkspace = $this->userWorkspaces()
            ->where('workspace_id', $workspaceId)
            ->with('role')
            ->first();

        return $userWorkspace?->role;
    }

    public function getNameAttribute()
    {
        return $this->full_name;
    }
}
