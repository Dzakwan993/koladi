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
            $table->uuid('user_id');
            $table->uuid('workspace_id')->nullable();
            $table->string('project_name');
            $table->json('payload');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('workspace_id')
                ->references('id')->on('workspaces')
                ->onDelete('cascade');
        });

        // Indexes
        DB::statement('CREATE INDEX idx_ai_processing_logs_workspace_id ON public.ai_processing_logs USING btree (workspace_id)');
        DB::statement('CREATE INDEX idx_ai_processing_logs_user_id ON public.ai_processing_logs USING btree (user_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_processing_logs');
    }
};
