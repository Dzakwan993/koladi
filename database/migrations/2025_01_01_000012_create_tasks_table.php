<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('status', 100)->nullable();
            $table->foreignUuid('board_column_id')->nullable()->constrained('board_columns');
            $table->string('priority', 50)->nullable();
            $table->boolean('is_secret')->nullable()->default(false);
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('due_datetime')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            $table->string('phase', 100)->nullable();
            $table->timestampTz('completed_at')->nullable();

            $table->index(['workspace_id', 'board_column_id'], 'idx_tasks_workspace_column');
            $table->index(['workspace_id', 'created_by'], 'idx_tasks_workspace_creator');
            $table->index(['workspace_id', 'is_secret'], 'idx_tasks_workspace_secret');
        });

        // Partial indexes in PostgreSQL
        DB::statement('CREATE INDEX idx_tasks_due_datetime ON public.tasks USING btree (due_datetime) WHERE (deleted_at IS NULL)');
        DB::statement('CREATE INDEX idx_tasks_status ON public.tasks USING btree (status) WHERE (deleted_at IS NULL)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
