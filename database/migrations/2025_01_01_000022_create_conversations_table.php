<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces');
            $table->string('type', 50)->nullable()->default('group');
            $table->string('name', 255)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->uuid('last_message_id')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('cascade');

            $table->index('last_message_id', 'idx_conversations_last_message_id');
        });

        // Add PostgreSQL ENUM column 'scope'
        DB::statement("ALTER TABLE public.conversations ADD COLUMN scope public.conversation_scope DEFAULT 'workspace'::public.conversation_scope NOT NULL");
        DB::statement("CREATE INDEX conversations_scope_company_id_index ON public.conversations USING btree (scope, company_id)");
        DB::statement("CREATE INDEX conversations_scope_workspace_id_index ON public.conversations USING btree (scope, workspace_id)");
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
