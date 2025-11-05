<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Checklist extends Model
{
    use HasFactory;

    protected $table = 'checklists';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'task_id',
        'title',
        'is_done',
        'position'
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
            if (!$model->position) {
                $model->position = static::where('task_id', $model->task_id)->max('position') + 1;
            }
        });
    }

    // Relasi ke Task
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    // Scope untuk item yang sudah selesai
    public function scopeDone($query)
    {
        return $query->where('is_done', true);
    }

    // Scope untuk item yang belum selesai
    public function scopePending($query)
    {
        return $query->where('is_done', false);
    }

    // Scope untuk mengurutkan berdasarkan position
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}