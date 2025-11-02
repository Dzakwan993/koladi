<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Label extends Model
{
    use HasFactory;

    protected $table = 'labels';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'color_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relasi ke Color
    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id');
    }

    // Relasi many-to-many ke Tasks (jika ada pivot table task_labels)
    // public function tasks()
    // {
    //     return $this->belongsToMany(Task::class, 'task_labels', 'label_id', 'task_id');
    // }
}