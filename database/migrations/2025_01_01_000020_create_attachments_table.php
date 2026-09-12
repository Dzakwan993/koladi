<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->string('attachable_type', 100)->nullable();
            $table->uuid('attachable_id')->nullable();
            $table->text('file_url');
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users');
            $table->timestamp('uploaded_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('file_name', 255)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type', 100)->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['attachable_type', 'attachable_id'], 'idx_attachments_attachable');
            $table->index('uploaded_by', 'idx_attachments_uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
