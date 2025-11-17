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

        // Buat default columns hanya untuk workspace ini
        static::created(function ($workspace) {
            $defaultColumns = [
                ['name' => 'To Do List', 'position' => 1],
                ['name' => 'Dikerjakan', 'position' => 2],
                ['name' => 'Selesai', 'position' => 3],
                ['name' => 'Batal', 'position' => 4],
            ];

            foreach ($defaultColumns as $column) {
                BoardColumn::create([
                    'id' => Str::uuid()->toString(),
                    'workspace_id' => $workspace->id,
                    'name' => $column['name'],
                    'position' => $column['position'],
                    'created_by' => $workspace->created_by,
                ]);
            }
        });
    }

    // ===== RELASI =====
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function boardColumns()
    {
        return $this->hasMany(BoardColumn::class, 'workspace_id')
            ->orderBy('position');
    }

    public function userWorkspaces()
    {
        return $this->hasMany(UserWorkspace::class, 'workspace_id');
    }

    /**
     * ✅ FIX: Relationship untuk mendapatkan members (users) dari workspace
     * Tanpa filter status_active di sini karena menyebabkan ambiguous column
     */
    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'user_workspaces',
            'workspace_id',
            'user_id'
        )
            ->withPivot('roles_id', 'status_active', 'join_date')
            ->using(UserWorkspace::class) // ✅ Gunakan pivot model
            ->withTimestamps();
    }

    /**
     * ✅ FIX: Relationship untuk members yang aktif saja
     * Gunakan method terpisah dengan wherePivot yang benar
     */
    public function activeMembers()
    {
        return $this->belongsToMany(
            User::class,
            'user_workspaces',
            'workspace_id',
            'user_id'
        )
            ->withPivot('roles_id', 'status_active', 'join_date')
            ->wherePivot('status_active', true)
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'workspace_id');
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class, 'workspace_id');
    }

    // ===== SCOPES =====
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * ✅ Scope untuk filter workspace yang bisa diakses user tertentu
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->whereHas('userWorkspaces', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('status_active', true);
        });
    }

    // ===== HELPER METHODS =====

    /**
     * ✅ Cek apakah user adalah member dari workspace ini
     */
    public function hasMember($userId)
    {
        return $this->userWorkspaces()
            ->where('user_id', $userId)
            ->where('status_active', true)
            ->exists();
    }

    /**
     * ✅ Get role user di workspace ini
     */
    public function getMemberRole($userId)
    {
        $userWorkspace = $this->userWorkspaces()
            ->where('user_id', $userId)
            ->with('role')
            ->first();

        return $userWorkspace?->role;
    }
}
