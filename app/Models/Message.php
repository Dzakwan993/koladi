<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'conversation_id',
        'sender_id',
        'content',
        'message_type',
        'reply_to_message_id', // 🔥 TAMBAHKAN INI
        'is_edited',
        'is_read',
        'edited_at',
        'read_at',
        'deleted_at'
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_read' => 'boolean',
        'edited_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($message) {
            $message->id = $message->id ?? Str::uuid();
        });
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ✅ Relasi untuk reply message
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id')
            ->with('sender'); // Auto load sender
    }

    // ✅ Relasi untuk messages yang reply ke message ini
    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // 🆕 Method untuk cek apakah pesan bisa diedit
    public function canBeEdited()
    {
        // Tidak bisa edit pesan yang sudah dihapus
        if ($this->deleted_at !== null) {
            return false;
        }

        // Hanya bisa edit dalam 15 menit
        $fifteenMinutesAgo = now()->subMinutes(15);
        return $this->created_at >= $fifteenMinutesAgo;
    }
}
