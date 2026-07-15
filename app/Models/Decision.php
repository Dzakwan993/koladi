<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Decision extends Model
{
    use HasFactory;

    protected $table = 'decisions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'workspace_id',
        'created_by',
        'title',
        'description',
        'decision_date',
        'evidence_file_id',
        'is_validated',
        'validated_by',
        'validated_at',
    ];

    protected $casts = [
        'decision_date' => 'date',
        'validated_at'  => 'datetime',
        'is_validated'  => 'boolean',
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

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evidenceFile()
    {
        return $this->belongsTo(File::class, 'evidence_file_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
