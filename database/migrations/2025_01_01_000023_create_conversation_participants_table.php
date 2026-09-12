<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('conversation_id')->nullable()->constrained('conversations')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->boolean('is_admin')->nullable()->default(false);
            $table->timestamp('last_read_at')->nullable();

            $table->index('conversation_id', 'idx_conversation_participants_conversation_id');
            $table->index('user_id', 'idx_conversation_participants_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
