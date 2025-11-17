<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserWorkspace extends Model
{
    use HasFactory;

    protected $table = 'user_workspaces';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'workspace_id',
        'roles_id',
        'status_active',
        'join_date' // ✅ Tambahkan ini
    ];

    // ✅ TAMBAHAN BARU: Tambahkan cast untuk kolom
    protected $casts = [
        'status_active' => 'boolean',
        'join_date' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
            // ✅ TAMBAHAN BARU: Auto set join_date jika belum ada
            $model->join_date = $model->join_date ?: now();
        });
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Workspace
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    // Relasi ke Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id');
    }

    // Scope untuk anggota aktif
    public function scopeActive($query)
    {
        return $query->where('status_active', true);
    }

    // ✅ TAMBAHAN BARU: Scope untuk anggota inactive
    public function scopeInactive($query)
    {
        return $query->where('status_active', false);
    }
}
