<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mindmap extends Model
{
    protected $fillable = ['workspace_id', 'title'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function nodes()
    {
        return $this->hasMany(MindmapNode::class);
    }
}