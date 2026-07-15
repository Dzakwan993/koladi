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
            $table->uuid('workspace_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('decision_date');
            $table->uuid('evidence_file_id')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->uuid('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('created_at')->default(DB::raw('now()'));
            $table->timestamp('updated_at')->default(DB::raw('now()'));

            // Foreign Keys
            $table->foreign('workspace_id')
                ->references('id')->on('workspaces')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->foreign('evidence_file_id')
                ->references('id')->on('files')
                ->onDelete('set null');

            $table->foreign('validated_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });

        // Indexes
        DB::statement('CREATE INDEX idx_decisions_workspace_id ON public.decisions USING btree (workspace_id)');
        DB::statement('CREATE INDEX idx_decisions_evidence_file_id ON public.decisions USING btree (evidence_file_id)');
        DB::statement('CREATE INDEX idx_decisions_decision_date ON public.decisions USING btree (decision_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
