<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        // 🔥 PERBAIKAN KRUSIAL: Load dengan nama relasi yang BENAR (reply_to, bukan replyTo)
        $message->load([
            'sender:id,full_name,email,avatar',
            'attachments',
            'reply_to' => function ($query) {  // 🔥 INI YANG PENTING: reply_to (snake_case)
                $query->select('id', 'sender_id', 'content', 'message_type', 'deleted_at', 'created_at')
                    ->with([
                        'sender:id,full_name,email,avatar',
                        'attachments'
                    ]);
            }
        ]);

        $this->message = $message;

        // 🔥 DEBUG: Verifikasi loading
        Log::info('🎯 NewMessageSent event created', [
            'message_id' => $message->id,
            'reply_to_message_id' => $message->reply_to_message_id,
            'has_reply_to_relation' => $message->relationLoaded('reply_to'),
            'reply_to_exists' => !is_null($message->reply_to),
            'reply_to_content' => $message->reply_to?->content ?? 'null'
        ]);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.' . $this->message->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return 'NewMessageSent';
    }

    public function broadcastWith(): array
    {
        // 🔥 PERBAIKAN: Gunakan toArray() untuk auto-serialization yang benar
        $messageArray = $this->message->toArray();

        // 🔥 DEBUG: Log apa yang akan di-broadcast
        Log::info('📤 Broadcasting message data', [
            'message_id' => $this->message->id,
            'has_reply_to_key' => array_key_exists('reply_to', $messageArray),
            'reply_to_is_null' => $messageArray['reply_to'] ?? 'key not exists',
            'sample_data' => [
                'id' => $messageArray['id'],
                'content' => $messageArray['content'],
                'reply_to_message_id' => $messageArray['reply_to_message_id'] ?? null,
                'reply_to' => $messageArray['reply_to'] ?? 'not found'
            ]
        ]);

        return [
            'message' => $messageArray
        ];
    }

    public function broadcastAfterCommit(): bool
    {
        return true;
    }
}
