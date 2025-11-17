<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    public $incrementing = false;      // ← TAMBAHKAN INI
    protected $keyType = 'string';     // ← TAMBAHKAN INI

    protected $fillable = [
        'workspace_id',
        'name',
        'is_private',
        'created_by',
        'parent_id', 
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    // Relasi ke folder induk
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    // Relasi ke subfolder
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }
}
