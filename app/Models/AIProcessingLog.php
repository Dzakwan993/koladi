<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIProcessingLog extends Model
{
    use HasFactory;

    protected $table = 'ai_processing_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'workspace_id',
        'project_name',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
