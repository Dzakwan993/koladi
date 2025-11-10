<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BoardColumn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'board_columns';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'workspace_id',
        'name',
        'position',
        'created_by'
    ];

    protected $casts = [
        'position' => 'integer',
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

    // Relasi ke Tasks
    public function tasks()
    {
        return $this->hasMany(Task::class, 'board_column_id');
    }

    // Scope untuk mengurutkan berdasarkan posisi
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    // Scope aktif (tidak terhapus)
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}