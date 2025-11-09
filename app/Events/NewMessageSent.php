<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
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
        $this->message = $message->load('sender');

        // 🔥 LOG untuk debugging
        Log::info('NewMessageSent Event Created', [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id
        ]);
    }

    public function broadcastOn(): array
    {
        $channel = new PrivateChannel('conversation.' . $this->message->conversation_id);

        // 🔥 LOG channel
        Log::info('Broadcasting on channel', [
            'channel' => 'conversation.' . $this->message->conversation_id
        ]);

        return [$channel];
    }

    public function broadcastAs(): string
    {
        // 🔥 PENTING: Ini nama event yang akan didengar frontend
        return 'NewMessage';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'message_type' => $this->message->message_type,
                'created_at' => $this->message->created_at->toISOString(),
                'sender' => [
                    'id' => $this->message->sender->id,
                    'full_name' => $this->message->sender->full_name,
                ],
                'attachments' => $this->message->attachments->map(function ($att) {
                    return [
                        'id' => $att->id,
                        'file_name' => $att->file_name,
                        'file_size' => $att->file_size,
                        'file_type' => $att->file_type,
                        'url' => url('storage/' . $att->file_url), // 🔥 PERBAIKAN DI SINI
                        'formatted_size' => $att->formatted_size,
                    ];
                })
            ]
        ];
    }
}
