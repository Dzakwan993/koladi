<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
                $defaultColumn = BoardColumn::where('workspace_id', $model->workspace_id)
                    ->where('name', 'like', '%To Do%')
                    ->first();
                    
                if ($defaultColumn) {
                    $model->board_column_id = $defaultColumn->id;
                }
            }
        });
    }

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

    // Relasi many-to-many ke Users melalui TaskAssignment
    public function assignees()
{
    return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
        ->withPivot(['assigned_at']) // Hanya ambil assigned_at, jangan timestamps
        ->using(TaskAssignment::class); // Specify custom pivot model
}

    // Relasi ke TaskAssignments
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

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
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Relasi ke Labels
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_labels', 'task_id', 'label_id')
            ->withTimestamps();
    }

    // Relasi ke TaskLabels (jika menggunakan model pivot)
    public function taskLabels()
    {
        return $this->hasMany(TaskLabel::class, 'task_id');
    }

    // ===== SCOPES =====
    
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

    // ===== HELPER METHODS =====

    public function isOverdue()
    {
        return $this->due_datetime && $this->due_datetime->lt(now()) 
               && !in_array($this->status, ['done', 'cancel']);
    }

    public function isSecret()
    {
        return $this->is_secret;
    }

    public function getProgressPercentage()
    {
        if ($this->checklists->count() === 0) {
            return 0;
        }

        $completed = $this->checklists->where('is_done', true)->count();
        return round(($completed / $this->checklists->count()) * 100);
    }

    // Validation rules
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
            'board_column_id' => 'nullable|exists:board_columns,id'
        ];

        if ($forCreate) {
            $rules['created_by'] = 'required|exists:users,id';
        }

        return $rules;
    }
}