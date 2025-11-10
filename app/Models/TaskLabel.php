<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskLabel extends Pivot
{
    use HasFactory;

    protected $table = 'task_labels';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'task_id',
        'label_id'
    ];

    protected $casts = [
        'id' => 'string',
        'task_id' => 'string',
        'label_id' => 'string'
    ];
}