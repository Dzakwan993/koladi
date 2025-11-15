<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'content',
        'commentable_id',
        'commentable_type',
        'parent_comment_id',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke model yang bisa dikomentari (Task)
    public function commentable()
    {
        return $this->morphTo();
    }

    // Relasi untuk reply
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id')->orderBy('created_at', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }
}