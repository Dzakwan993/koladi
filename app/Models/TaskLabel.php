<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskLabel extends Pivot
{
    use HasFactory;

    protected $table = 'task_labels';



    protected $fillable = [
        'task_id',
        'label_id'
    ];

    protected $casts = [
        'task_id' => 'string',
        'label_id' => 'string'
    ];
}
