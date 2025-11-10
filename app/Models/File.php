<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'folder_id',
        'workspace_id',
        'file_url',
        'is_private',
        'uploaded_by'
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'uploaded_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relasi ke Folder
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    // Relasi ke Workspace
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    // Relasi ke User (uploader)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}