<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['task_id', 'user_id'], 'idx_task_assignments_task_user');
            $table->index('user_id', 'idx_task_assignments_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
