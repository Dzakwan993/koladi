<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'company_id',
        'type',
        'name',
        'description',
        'created_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi ke User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User_Workspaces
    public function userWorkspaces()
    {
        return $this->hasMany(UserWorkspace::class, 'workspace_id');
    }

    // Relasi many-to-many ke Users melalui User_Workspaces
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_workspaces', 'workspace_id', 'user_id')
            ->withPivot('roles_id', 'status_active')
            ->withTimestamps();
    }

    // Scope untuk workspace aktif
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Scope berdasarkan type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}