<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

        static::updated(function ($workspace) {
            if ($workspace->isDirty('name')) {
                $mainConversation = Conversation::where('workspace_id', $workspace->id)
                    ->where('type', 'group')
                    ->first();

                if ($mainConversation) {
                    $mainConversation->update(['name' => $workspace->name]);
                    Log::info("Auto-updated conversation name from '{$workspace->getOriginal('name')}' to '{$workspace->name}'");
                }
            }
        });

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
     * ✅ Relationship untuk mendapatkan members (users) dari workspace
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
            ->withTimestamps();
    }

    // 🔥 TAMBAHKAN METHOD INI - Alias untuk members()
    /**
     * ✅ Alias untuk members() - digunakan di ChatController
     * Untuk konsistensi dengan Company model yang juga menggunakan users()
     */
    public function users()
    {
        return $this->members();
    }

    public function mainConversation()
    {
        return $this->hasOne(Conversation::class, 'workspace_id')
            ->where('type', 'group');
    }

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

     public function pengumumans()
    {
        return $this->hasMany(Pengumuman::class);
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->whereHas('userWorkspaces', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('status_active', true);
        });
    }

    // ===== HELPER METHODS =====

    public function hasMember($userId)
    {
        return $this->userWorkspaces()
            ->where('user_id', $userId)
            ->where('status_active', true)
            ->exists();
    }

    public function getMemberRole($userId)
    {
        $userWorkspace = $this->userWorkspaces()
            ->where('user_id', $userId)
            ->with('role')
            ->first();

        return $userWorkspace?->role;
    }
    //relasi mindmap
    public function mindmaps()
    {
        return $this->hasMany(Mindmap::class, 'workspace_id');
    }
}
