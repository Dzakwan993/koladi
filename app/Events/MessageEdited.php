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

class MessageEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        // ✅ PERBAIKAN: Load SEMUA relasi termasuk replyTo
        $this->message = $message->load([
            'sender',
            'attachments',
            'replyTo.sender',      // ✅ TAMBAH INI
            'replyTo.attachments'  // ✅ TAMBAH INI
        ]);

        // 🔥 DEBUG: Log event creation
        Log::info('MessageEdited event created', [
            'message_id' => $message->id,
            'has_reply_to' => !is_null($message->reply_to_message_id),
            'reply_to_loaded' => !is_null($message->replyTo)
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastAs()
    {
        return 'MessageEdited';
    }

    /**
     * ✅ PERBAIKAN: Tambahkan broadcastWith() untuk kirim data lengkap
     */
    public function broadcastWith()
    {
        $data = [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'message_type' => $this->message->message_type,
                'reply_to_message_id' => $this->message->reply_to_message_id,
                'is_edited' => $this->message->is_edited,
                'edited_at' => $this->message->edited_at,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,

                'sender' => $this->message->sender ? [
                    'id' => $this->message->sender->id,
                    'full_name' => $this->message->sender->full_name,
                    'email' => $this->message->sender->email,
                    'avatar' => $this->message->sender->avatar,
                ] : null,

                'attachments' => $this->message->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'file_url' => $attachment->file_url,
                        'file_name' => $attachment->file_name,
                        'file_size' => $attachment->file_size,
                        'file_type' => $attachment->file_type,
                    ];
                })->toArray(),

                // ✅✅✅ PERBAIKAN KRUSIAL: Include replyTo data
                'replyTo' => $this->message->replyTo ? [
                    'id' => $this->message->replyTo->id,
                    'sender_id' => $this->message->replyTo->sender_id,
                    'content' => $this->message->replyTo->content,
                    'message_type' => $this->message->replyTo->message_type,
                    'deleted_at' => $this->message->replyTo->deleted_at,
                    'created_at' => $this->message->replyTo->created_at,
                    'sender' => $this->message->replyTo->sender ? [
                        'id' => $this->message->replyTo->sender->id,
                        'full_name' => $this->message->replyTo->sender->full_name,
                        'email' => $this->message->replyTo->sender->email,
                        'avatar' => $this->message->replyTo->sender->avatar,
                    ] : null,
                    'attachments' => $this->message->replyTo->attachments->map(function ($attachment) {
                        return [
                            'id' => $attachment->id,
                            'file_url' => $attachment->file_url,
                            'file_name' => $attachment->file_name,
                            'file_size' => $attachment->file_size,
                            'file_type' => $attachment->file_type,
                        ];
                    })->toArray()
                ] : null
            ]
        ];

        // 🔥 DEBUG: Log data yang akan di-broadcast
        Log::info('MessageEdited broadcasting data', [
            'message_id' => $this->message->id,
            'has_reply_to' => !is_null($this->message->reply_to_message_id),
            'reply_to_loaded' => !is_null($data['message']['replyTo']),
            'reply_to_content' => $data['message']['replyTo']['content'] ?? 'null'
        ]);

        return $data;
    }

    /**
     * 🔥 CRITICAL: Broadcast harus SETELAH database commit
     */
    public function broadcastAfterCommit()
    {
        return true;
    }
}
