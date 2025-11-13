<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'workspace_id',
        'company_id',      // Untuk scope 'company'
        'scope',           // 'workspace' atau 'company'
        'type',            // 'group', 'private'
        'name',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($conversation) {
            $conversation->id = $conversation->id ?? Str::uuid();

            // Auto-set scope berdasarkan company_id atau workspace_id
            if (!$conversation->scope) {
                if ($conversation->company_id) {
                    $conversation->scope = 'company';
                } else {
                    $conversation->scope = 'workspace';
                }
            }
        });
    }

    // ============================================
    // SCOPES - Query Helpers
    // ============================================

    public function scopeWorkspaceScope($query, $workspaceId)
    {
        return $query->where('scope', 'workspace')
            ->where('workspace_id', $workspaceId);
    }

    public function scopeCompanyScope($query, $companyId)
    {
        return $query->where('scope', 'company')
            ->where('company_id', $companyId);
    }

    public function scopeGroupType($query)
    {
        return $query->where('type', 'group');
    }

    public function scopePrivateType($query)
    {
        return $query->where('type', 'private');
    }

    // Scope untuk main group conversation
    public function scopeMainGroup($query, $workspaceId = null, $companyId = null)
    {
        $query->where('type', 'group');

        if ($workspaceId) {
            return $query->where('scope', 'workspace')
                ->where('workspace_id', $workspaceId);
        }

        if ($companyId) {
            return $query->where('scope', 'company')
                ->where('company_id', $companyId);
        }

        return $query;
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')
            ->latest()
            ->with('sender', 'attachments');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    public function getLastMessageAttribute()
    {
        return $this->messages()
            ->with('sender', 'attachments')
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function isCompanyChat()
    {
        return $this->scope === 'company';
    }

    public function isWorkspaceChat()
    {
        return $this->scope === 'workspace';
    }

    public function isMainGroup()
    {
        return $this->type === 'group' &&
            (($this->scope === 'workspace' && $this->workspace_id) ||
                ($this->scope === 'company' && $this->company_id));
    }
}

