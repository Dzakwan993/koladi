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
        'type',
        'name',
        'created_by'
    ];

    protected static function booted()
    {
        static::creating(function ($conversation) {
            $conversation->id = $conversation->id ?? Str::uuid();
        });
    }

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 🔥🔥🔥 PERBAIKAN DI SINI 🔥🔥🔥

    /**
     * Relasi untuk mendapatkan pesan terakhir dengan eager loading
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')
            ->latest() // Ini akan otomatis order by created_at DESC
            ->with('sender', 'attachments'); // Include sender & attachments
    }

    /**
     * 🔥 ALTERNATIF: Jika masih bermasalah, pakai accessor
     */
    public function getLastMessageAttribute()
    {
        return $this->messages()
            ->with('sender', 'attachments')
            ->orderBy('created_at', 'DESC')
            ->first();
    }
}
