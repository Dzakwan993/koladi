<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mindmaps', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('workspace_id')->constrained('workspaces')->onDelete('cascade');
            $table->string('title', 255)->default('Mind Map Utama');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('mindmap_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('mindmap_id')->constrained('mindmaps')->onDelete('cascade');
            $table->uuid('parent_id')->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('type', 50)->nullable()->default('default');
            $table->decimal('x_position', 10, 2)->nullable()->default(0);
            $table->decimal('y_position', 10, 2)->nullable()->default(0);
            $table->string('connection_side', 20)->nullable()->default('auto');
            $table->integer('sort_order')->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('parent_id')->references('id')->on('mindmap_nodes')->onDelete('cascade');
            $table->index('mindmap_id', 'idx_mindmap_nodes_mindmap_id');
            $table->index('parent_id', 'idx_mindmap_nodes_parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mindmap_nodes');
        Schema::dropIfExists('mindmaps');
    }
};
