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

    // -- relations --


    protected $appends = ['author'];

public function getAuthorAttribute()
{
    if (!$this->user) return null;

    return [
        'id' => $this->user->id,
        'name' => $this->user->name,
        'avatar' => $this->user->avatar ?? null,
    ];
}

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    // replies (one level nested)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id')->orderBy('created_at');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    // attachments for this comment (optional, recommended)
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // accessor formatted date
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y H:i') : null;
    }
}
