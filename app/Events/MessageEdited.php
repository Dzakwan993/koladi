<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageEdited implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        // 🔥 Load dengan reply_to
        $message->load([
            'sender:id,full_name,avatar',
            'attachments',
            'reply_to' => function ($query) {  // 🔥 reply_to
                $query->select('id', 'sender_id', 'content', 'message_type', 'deleted_at', 'created_at')
                    ->with('sender:id,full_name,avatar', 'attachments');
            }
        ]);

        $this->message = $message;
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('conversation.' . $this->message->conversation_id)];
    }

    public function broadcastAs(): string
    {
        return 'MessageEdited';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message->toArray()
        ];
    }
}
