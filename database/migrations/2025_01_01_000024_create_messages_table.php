<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('conversation_id')->nullable()->constrained('conversations')->onDelete('cascade');
            $table->foreignUuid('sender_id')->nullable()->constrained('users');
            $table->text('content')->nullable();
            $table->string('message_type', 50)->nullable();
            $table->uuid('reply_to_message_id')->nullable();
            $table->boolean('is_edited')->nullable()->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->softDeletes();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->boolean('is_read')->nullable()->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('reply_to_message_id')->references('id')->on('messages');
            $table->index('conversation_id', 'idx_messages_conversation_id');
        });

        // Add deferred FK from conversations.last_message_id to messages.id
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('last_message_id', 'fk_conversations_last_message')
                ->references('id')
                ->on('messages')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign('fk_conversations_last_message');
        });
        Schema::dropIfExists('messages');
    }
};
