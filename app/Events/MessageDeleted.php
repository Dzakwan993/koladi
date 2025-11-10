<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message_id;
    public $conversation_id;
    public $sender_id;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message_id = $message->id;
        $this->conversation_id = $message->conversation_id;
        $this->sender_id = $message->sender_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->conversation_id),
        ];
    }

    /**
     * 🔥 PENTING: Nama event yang akan di-broadcast
     * Harus match dengan listener di JavaScript: .MessageDeleted
     */
    public function broadcastAs(): string
    {
        return 'MessageDeleted';
    }

    /**
     * 🔥 Data yang dikirim ke client
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message_id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
        ];
    }
}
