<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'workspace_id',
        'created_by',
        'title',
        'description',
        'status',
        'board_column_id',
        'priority',
        'is_secret',
        'start_datetime',
        'due_datetime',
        'phase'
    ];

    protected $attributes = [
        'is_secret' => false,
        'priority' => 'medium',
        'status' => 'todo'
    ];

    protected $casts = [
        'is_secret' => 'boolean',
        'start_datetime' => 'datetime',
        'due_datetime' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
            
            // Set default board column jika tidak disediakan
            if (!$model->board_column_id) {
                try {
                    $defaultColumn = BoardColumn::where('workspace_id', $model->workspace_id)
                        ->where('name', 'like', '%To Do%')
                        ->first();
                        
                    if ($defaultColumn) {
                        $model->board_column_id = $defaultColumn->id;
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to set default board column: ' . $e->getMessage());
                }
            }
        });

        static::created(function ($model) {
            Log::info("Task created: {$model->id} - {$model->title}");
        });

        static::updated(function ($model) {
            Log::info("Task updated: {$model->id} - {$model->title}");
        });

        static::deleted(function ($model) {
            Log::info("Task deleted: {$model->id} - {$model->title}");
        });
    }

    // ===== RELASI UTAMA =====

    // Relasi ke Workspace
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    // Relasi ke User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke BoardColumn
    public function boardColumn()
    {
        return $this->belongsTo(BoardColumn::class, 'board_column_id');
    }

    // ===== RELASI ASSIGNMENTS =====

    // ✅ OPTION 1: Relasi many-to-many langsung (RECOMMENDED)
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->withPivot('assigned_at')
            ->withTimestamps(false);
    }

    // ✅ OPTION 2: Relasi melalui model TaskAssignment (jika butuh lebih banyak logika)
    public function assignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

    // ✅ OPTION 3: Relasi dengan custom pivot (jika TaskAssignment extends Pivot)
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->using(TaskAssignment::class)
            ->withPivot('assigned_at');
    }

    public function syncStatusFromColumn()
{
    if (!$this->boardColumn) {
        return;
    }

    // Mapping nama kolom default ke status
    $columnStatusMap = [
        'To Do List' => 'todo',
        'Dikerjakan' => 'inprogress', 
        'Selesai' => 'done',
        'Batal' => 'cancel'
    ];

    $columnName = $this->boardColumn->name;
    
    if (array_key_exists($columnName, $columnStatusMap)) {
        // Untuk kolom default, gunakan mapping
        $this->status = $columnStatusMap[$columnName];
    } else {
        // Untuk kolom custom, gunakan nama kolom sebagai status
        // Konversi ke lowercase dan replace spasi dengan underscore
        $this->status = strtolower(str_replace(' ', '_', $columnName));
    }
}




    // ===== RELASI LAINNYA =====

    // Relasi ke Checklists
    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'task_id')->orderBy('position');
    }

    // Relasi ke Attachments
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Relasi ke Comments
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'desc');
    }

    // Relasi ke Labels
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_labels', 'task_id', 'label_id')
            ->withTimestamps();
    }

    // Relasi ke TaskLabels (pivot table)
    public function taskLabels()
    {
        return $this->hasMany(TaskLabel::class, 'task_id');
    }

    // ===== SCOPES =====
    
    // Scope untuk tugas berdasarkan akses (secret/non-secret)
    public function scopeWithAccess($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('is_secret', false)
              ->orWhere('created_by', $user->id)
              ->orWhereHas('assignments', function ($assignmentQuery) use ($user) {
                  $assignmentQuery->where('user_id', $user->id);
              });
        });
    }

    // Scope untuk tugas rahasia
    public function scopeSecret($query)
    {
        return $query->where('is_secret', true);
    }

    // Scope untuk tugas non-rahasia
    public function scopeNonSecret($query)
    {
        return $query->where('is_secret', false);
    }

    // Scope berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope berdasarkan prioritas
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Scope untuk tugas yang belum selesai
    public function scopeIncomplete($query)
    {
        return $query->where('status', '!=', 'done');
    }

    // Scope untuk tugas yang sudah lewat deadline
    public function scopeOverdue($query)
    {
        return $query->where('due_datetime', '<', now())
                    ->whereNotIn('status', ['done', 'cancel']);
    }

    // Scope untuk tugas yang akan datang
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now())
                    ->where('status', 'todo');
    }

    // Scope berdasarkan workspace
    public function scopeByWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('phase', 'like', "%{$searchTerm}%");
        });
    }

    // ===== HELPER METHODS =====

    /**
     * Cek apakah tugas sudah lewat deadline
     */
    public function isOverdue()
    {
        return $this->due_datetime && 
               $this->due_datetime->lt(now()) && 
               !in_array($this->status, ['done', 'cancel']);
    }

    /**
     * Cek apakah tugas rahasia
     */
    public function isSecret()
    {
        return $this->is_secret;
    }

    /**
     * Hitung persentase progress berdasarkan checklist
     */
    public function getProgressPercentage()
    {
        if ($this->checklists->count() === 0) {
            return 0;
        }

        $completed = $this->checklists->where('is_done', true)->count();
        return round(($completed / $this->checklists->count()) * 100);
    }

    /**
     * Cek apakah user memiliki akses ke tugas ini
     */
    public function userHasAccess($user)
    {
        // Super admin/administrator selalu punya akses
        $userCompany = $user->userCompanies()
            ->where('company_id', $this->workspace->company_id)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin'])) {
            return true;
        }

        // User biasa hanya bisa akses jika:
        // 1. Tugas tidak rahasia, ATAU
        // 2. User adalah creator tugas, ATAU  
        // 3. User adalah assigned member
        return !$this->is_secret || 
               $this->created_by === $user->id || 
               $this->assignments()->where('user_id', $user->id)->exists();
    }

    /**
     * Assign user ke tugas
     */
    public function assignUser($userId)
    {
        return TaskAssignment::create([
            'id' => Str::uuid()->toString(),
            'task_id' => $this->id,
            'user_id' => $userId,
            'assigned_at' => now()
        ]);
    }

    /**
     * Unassign user dari tugas
     */
    public function unassignUser($userId)
    {
        return $this->assignments()->where('user_id', $userId)->delete();
    }

    /**
     * Update status tugas
     */
    public function updateStatus($status)
    {
        $validStatuses = ['todo', 'inprogress', 'done', 'cancel'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Status tidak valid: {$status}");
        }

        $this->status = $status;
        return $this->save();
    }

    /**
     * Pindahkan tugas ke board column lain
     */
    public function moveToColumn($boardColumnId)
{
    $column = BoardColumn::find($boardColumnId);
    
    if (!$column || $column->workspace_id !== $this->workspace_id) {
        throw new \InvalidArgumentException("Board column tidak valid");
    }

    $this->board_column_id = $boardColumnId;
    $this->syncStatusFromColumn(); // Sync status otomatis
    
    return $this->save();
}

    /**
     * Duplikat tugas
     */
    public function duplicate($newTitle = null)
    {
        $newTask = $this->replicate();
        $newTask->id = Str::uuid()->toString();
        $newTask->title = $newTitle ?: $this->title . ' (Copy)';
        $newTask->status = 'todo';
        $newTask->created_at = now();
        $newTask->updated_at = now();
        $newTask->save();

        // Duplikat checklists
        foreach ($this->checklists as $checklist) {
            $newChecklist = $checklist->replicate();
            $newChecklist->id = Str::uuid()->toString();
            $newChecklist->task_id = $newTask->id;
            $newChecklist->save();
        }

        // Duplikat labels
        $newTask->labels()->attach($this->labels->pluck('id'));

        return $newTask;
    }

    /**
     * Format data untuk response API
     */
    public function toApiResponse()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'is_secret' => $this->is_secret,
            'phase' => $this->phase,
            'start_datetime' => $this->start_datetime?->toISOString(),
            'due_datetime' => $this->due_datetime?->toISOString(),
            'progress_percentage' => $this->getProgressPercentage(),
            'is_overdue' => $this->isOverdue(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'workspace' => $this->workspace?->only(['id', 'name']),
            'creator' => $this->creator?->only(['id', 'name', 'email']),
            'board_column' => $this->boardColumn?->only(['id', 'name', 'position']),
            'assigned_users' => $this->assignedUsers->map(function ($user) {
                return $user->only(['id', 'name', 'email', 'avatar']);
            }),
            'labels' => $this->labels->map(function ($label) {
                return [
                    'id' => $label->id,
                    'name' => $label->name,
                    'color' => $label->color?->rgb
                ];
            }),
            'checklists' => $this->checklists->map(function ($checklist) {
                return $checklist->only(['id', 'title', 'is_done', 'position']);
            }),
            'attachments_count' => $this->attachments->count(),
            'comments_count' => $this->comments->count()
        ];
    }

    // ===== VALIDATION RULES =====

    public static function rules($forCreate = true)
    {
        $rules = [
            'workspace_id' => 'required|exists:workspaces,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phase' => 'required|string|max:255',
            'status' => 'sometimes|in:todo,inprogress,done,cancel',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'is_secret' => 'boolean',
            'start_datetime' => 'nullable|date',
            'due_datetime' => 'nullable|date|after:start_datetime',
            'board_column_id' => 'nullable|exists:board_columns,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'label_ids' => 'array',
            'label_ids.*' => 'exists:labels,id'
        ];

        if ($forCreate) {
            $rules['created_by'] = 'required|exists:users,id';
        }

        return $rules;
    }

    // ===== ACCESSORS =====

    /**
     * Accessor untuk formatted due date
     */
    public function getFormattedDueDateAttribute()
    {
        if (!$this->due_datetime) {
            return null;
        }

        return $this->due_datetime->format('d M Y H:i');
    }

    /**
     * Accessor untuk waktu tersisa
     */
    public function getTimeRemainingAttribute()
    {
        if (!$this->due_datetime) {
            return null;
        }

        $now = now();
        $due = $this->due_datetime;

        if ($due->lt($now)) {
            return 'Terlambat';
        }

        $diff = $now->diff($due);

        if ($diff->days > 0) {
            return $diff->days . ' hari lagi';
        } elseif ($diff->h > 0) {
            return $diff->h . ' jam lagi';
        } else {
            return $diff->i . ' menit lagi';
        }
    }

    /**
     * Accessor untuk status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'todo' => 'gray',
            'inprogress' => 'blue', 
            'done' => 'green',
            'cancel' => 'red',
            default => 'gray'
        };
    }

    /**
     * Accessor untuk priority badge color
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }
}