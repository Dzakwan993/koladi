<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->string('type', 255);
            $table->string('title', 255);
            $table->text('message');
            $table->string('context', 255)->nullable();
            $table->string('notifiable_type', 255);
            $table->uuid('notifiable_id');
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at', 0)->nullable();
            $table->string('action_url', 255)->nullable();
            $table->dateTime('created_at', 0)->nullable();
            $table->dateTime('updated_at', 0)->nullable();

            $table->index('created_at', 'notifications_created_at_index');
            $table->index(['type', 'user_id'], 'notifications_type_user_id_index');
            $table->index(['user_id', 'company_id', 'is_read'], 'notifications_user_id_company_id_is_read_index');
        });

        DB::statement("ALTER TABLE public.notifications ADD CONSTRAINT notifications_type_check CHECK (((type)::text = ANY (ARRAY[('chat'::character varying)::text, ('task'::character varying)::text, ('announcement'::character varying)::text, ('schedule'::character varying)::text])))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
