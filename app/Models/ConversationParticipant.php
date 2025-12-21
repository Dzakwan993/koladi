<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ConversationParticipant extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'conversation_id',
        'user_id',
        'joined_at',
        'is_admin'
    ];

    protected static function booted()
    {
        static::creating(function ($participant) {
            $participant->id = $participant->id ?? Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
