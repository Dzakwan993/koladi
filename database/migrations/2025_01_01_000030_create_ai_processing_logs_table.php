<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_processing_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->string('project_name', 255);
            $table->json('payload');
            $table->dateTime('created_at', 0)->nullable();
            $table->dateTime('updated_at', 0)->nullable();

            $table->index('user_id', 'idx_ai_processing_logs_user_id');
            $table->index('workspace_id', 'idx_ai_processing_logs_workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_processing_logs');
    }
};
