<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'company_id',
        'workspace_id',
        'type',
        'title',
        'message',
        'context',
        'notifiable_type',
        'notifiable_id',
        'actor_id',
        'is_read',
        'read_at',
        'action_url',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Accessor untuk formatted time
    public function getFormattedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Accessor untuk actor info
    public function getActorInfoAttribute()
    {
        if (!$this->actor) {
            return [
                'name' => 'System',
                'avatar' => 'https://ui-avatars.com/api/?name=System&background=6B7280&color=fff',
            ];
        }

        return [
            'id' => $this->actor->id,
            'name' => $this->actor->full_name,
            'avatar' => $this->actor->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->actor->full_name) . '&background=4F46E5&color=fff',
        ];
    }
}