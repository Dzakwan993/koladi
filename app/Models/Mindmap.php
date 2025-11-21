<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mindmap extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'workspace_id',
        'title',
        'description'
    ];

    // Tentukan nama tabel secara eksplisit
    protected $table = 'mindmaps';

    // Tentukan kolom timestamps
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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

    // Relasi ke Nodes
    public function nodes()
    {
        return $this->hasMany(MindmapNode::class, 'mindmap_id')->orderBy('sort_order');
    }

    public function rootNodes()
    {
        return $this->hasMany(MindmapNode::class, 'mindmap_id')->whereNull('parent_id')->orderBy('sort_order');
    }
}
