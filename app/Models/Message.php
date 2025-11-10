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
        'reply_to_message_id',
        'is_edited',
        'is_read',
        'edited_at',
        'read_at',
        'deleted_at'  // ✅ Ini aman karena ada di fillable
    ];

    // 🔥 PENTING: Tambahkan cast untuk semua datetime fields
    protected $casts = [
        'is_edited' => 'boolean',
        'is_read' => 'boolean',
        'edited_at' => 'datetime',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',  // 🔥 INI YANG KURANG!
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

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    // 🔥 RELASI KE ATTACHMENTS (Polymorphic)
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
