<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MindmapNode extends Model
{
    protected $fillable = [
        'mindmap_id', 'title', 'description', 
        'x', 'y', 'type', 'parent_id', 
        'connection_side', 'is_root', 'created_by'
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'is_root' => 'boolean',
    ];

    public function mindmap()
    {
        return $this->belongsTo(Mindmap::class);
    }

    public function parent()
    {
        return $this->belongsTo(MindmapNode::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MindmapNode::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}