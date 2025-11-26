<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MindmapNode extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mindmap_id',
        'parent_id',
        'title',
        'description',
        'type',
        'x_position',
        'y_position',
        'connection_side',
        'sort_order'
    ];

    // Tentukan nama tabel secara eksplisit
    protected $table = 'mindmap_nodes';

    // Tentukan kolom timestamps
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
        'x_position' => 'float',
        'y_position' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relasi ke Mindmap
    public function mindmap()
    {
        return $this->belongsTo(Mindmap::class, 'mindmap_id');
    }

    // Relasi ke Parent Node
    public function parent()
    {
        return $this->belongsTo(MindmapNode::class, 'parent_id');
    }

    // Relasi ke Child Nodes
    public function children()
    {
        return $this->hasMany(MindmapNode::class, 'parent_id')->orderBy('sort_order');
    }
}
