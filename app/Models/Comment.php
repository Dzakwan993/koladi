<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use SoftDeletes;

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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Accessor untuk format data author
    protected $appends = ['author'];

    public function getAuthorAttribute()
    {
        if (!$this->user) {
            return [
                'id' => null,
                'name' => 'Unknown User',
                'avatar' => 'https://i.pravatar.cc/40?img=0',
            ];
        }

        return [
            'id' => $this->user->id,
            'name' => $this->user->full_name ?? $this->user->name,
            'avatar' => $this->user->avatar ?? 'https://i.pravatar.cc/40?img=' . rand(1, 70),
        ];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id')->orderBy('created_at', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
}