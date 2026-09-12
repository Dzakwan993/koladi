<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_performance_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_type', 10)->default('week');
            $table->jsonb('metrics');
            $table->integer('performance_score')->default(0);
            $table->integer('quality_score')->default(0);
            $table->integer('risk_score')->default(0);
            $table->jsonb('suggestions');
            $table->dateTime('created_at', 0)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('updated_at', 0)->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('version', 10)->default('1.0');

            $table->index('created_at', 'ws_perf_idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_performance_snapshots');
    }
};
