<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskAssignment extends Pivot
{
    protected $table = 'task_assignments';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'task_id', 
        'user_id',
        'assigned_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = \Illuminate\Support\Str::uuid()->toString();
            }
            if (empty($model->assigned_at)) {
                $model->assigned_at = now();
            }
        });
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}