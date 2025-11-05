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
            ->withTimestamps();
    }

    // Relasi ke TaskAssignments
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

    // Relasi ke Checklists
    // Di dalam Task model, tambahkan relasi:
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
        return $query->where('status', '!=', 'completed');
    }

    // Scope untuk tugas yang sudah lewat deadline
    public function scopeOverdue($query)
    {
        return $query->where('due_datetime', '<', now());
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'task_labels', 'task_id', 'label_id')
            ->using(TaskLabel::class)
            ->withTimestamps();
    }
}
