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

class NewMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Instance pesan yang baru dibuat.
     *
     * Properti 'public' akan otomatis ikut terkirim
     * sebagai payload ke frontend.
     */
    public Message $message;

    /**
     * Buat instance event baru.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Tentukan channel mana yang akan di-broadcast.
     *
     * Ini adalah bagian terpenting. Kita akan mengirim
     * pesan ini ke channel privat yang namanya unik
     * berdasarkan ID percakapan.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Channel ini akan 'didengarkan' oleh frontend
        // contoh: Echo.private('conversation.ID_PERCAKAPAN')
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }
}
