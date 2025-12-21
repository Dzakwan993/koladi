<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('company_id');
            $table->uuid('workspace_id')->nullable();
            
            // Notification Type: chat, task, announcement, schedule
            $table->enum('type', ['chat', 'task', 'announcement', 'schedule']);
            
            // Notification Content
            $table->string('title');
            $table->text('message');
            $table->string('context')->nullable(); // e.g., "Chat Pribadi · Workspace Utama"
            
            // Related Entity
            $table->string('notifiable_type'); // Polymorphic
            $table->uuid('notifiable_id'); // Polymorphic
            
            // Actor (who triggered the notification)
            $table->uuid('actor_id')->nullable();
            
            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Action URL (redirect target when clicked)
            $table->string('action_url')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'company_id', 'is_read']);
            $table->index(['type', 'user_id']);
            $table->index('created_at');
            
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};