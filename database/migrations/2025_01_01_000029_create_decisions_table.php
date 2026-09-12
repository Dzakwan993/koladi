<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('decision_date');
            $table->foreignUuid('evidence_file_id')->nullable()->constrained('files')->onDelete('set null');
            $table->boolean('is_validated')->nullable()->default(false);
            $table->foreignUuid('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('decision_date', 'idx_decisions_decision_date');
            $table->index('evidence_file_id', 'idx_decisions_evidence_file_id');
            $table->index('workspace_id', 'idx_decisions_workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
