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

    // ✅ Accessor untuk format data author (opsional, bisa digunakan atau tidak)
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

        // Generate avatar URL
        $avatarPath = $this->user->avatar ? 'storage/' . $this->user->avatar : null;
        $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

        $avatarUrl = $hasAvatarFile
            ? asset($avatarPath)
            : ($this->user->full_name
                ? 'https://ui-avatars.com/api/?name=' . urlencode($this->user->full_name) . '&background=random&color=fff'
                : 'https://i.pravatar.cc/40?img=' . rand(1, 70));

        return [
            'id' => $this->user->id,
            'name' => $this->user->full_name ?? $this->user->name,
            'avatar' => $avatarUrl,
        ];
    }

    // Helper method untuk mendapatkan avatar URL
    public function getAvatarUrl()
    {
        if (!$this->user) {
            return 'https://i.pravatar.cc/40?img=0';
        }

        $avatarPath = $this->user->avatar ? 'storage/' . $this->user->avatar : null;
        $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

        if ($hasAvatarFile) {
            return asset($avatarPath);
        }

        if ($this->user->full_name) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->user->full_name) . '&background=random&color=fff';
        }

        return 'https://i.pravatar.cc/40?img=' . rand(1, 70);
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
