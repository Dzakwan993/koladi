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

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;

        // 🔥 DEBUG: Log event creation
        Log::info('NewMessageSent event created', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channel = new PrivateChannel('conversation.' . $this->message->conversation_id);

        // 🔥 DEBUG: Log channel info
        Log::info('Broadcasting on channel', [
            'channel' => 'conversation.' . $this->message->conversation_id
        ]);

        return [$channel];
    }

    /**
     * 🔥 PENTING: Nama event yang akan di-broadcast
     * Harus match dengan listener di JavaScript: .NewMessageSent
     */
    public function broadcastAs(): string
    {
        return 'NewMessageSent';
    }

    /**
     * 🔥 Data yang dikirim ke client
     * PENTING: Kirim seluruh object message dengan relasi
     */
    public function broadcastWith(): array
    {
        // Pastikan relasi sudah di-load
        $this->message->load(['sender', 'attachments']);

        $data = [
            'message' => $this->message->toArray()
        ];

        // 🔥 DEBUG: Log data yang akan di-broadcast
        Log::info('Broadcasting data', [
            'message_id' => $this->message->id,
            'has_sender' => isset($data['message']['sender']),
            'has_attachments' => isset($data['message']['attachments']),
            'attachments_count' => count($data['message']['attachments'] ?? [])
        ]);

        return $data;
    }

    /**
     * 🔥 CRITICAL: Broadcast harus SETELAH database commit
     */
    public function broadcastAfterCommit(): bool
    {
        return true;
    }
}
