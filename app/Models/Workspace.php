<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'company_id',
        'type',
        'name',
        'description',
        'created_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });

        // Buat default columns hanya untuk workspace ini
        static::created(function ($workspace) {
            $defaultColumns = [
                ['name' => 'To Do List', 'position' => 1],
                ['name' => 'Dikerjakan', 'position' => 2],
                ['name' => 'Selesai', 'position' => 3],
                ['name' => 'Batal', 'position' => 4],
            ];

            foreach ($defaultColumns as $column) {
                BoardColumn::create([
                    'id' => Str::uuid()->toString(),
                    'workspace_id' => $workspace->id,
                    'name' => $column['name'],
                    'position' => $column['position'],
                    'created_by' => $workspace->created_by,
                ]);
            }

            // ❌ HAPUS BAGIAN INI: Creator TIDAK otomatis ditambahkan sebagai member workspace
            // SuperAdmin, Admin, Manager yang membuat workspace tidak menjadi member workspace
        });
    }

    // Relasi
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function boardColumns()
    {
        return $this->hasMany(BoardColumn::class, 'workspace_id')
            ->orderBy('position');
    }

    public function userWorkspaces()
    {
        return $this->hasMany(UserWorkspace::class, 'workspace_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'workspace_id');
    }

    // Scope
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

     public function pengumumans()
    {
        return $this->hasMany(Pengumuman::class);
    }
}
