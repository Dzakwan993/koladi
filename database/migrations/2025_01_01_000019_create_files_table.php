<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->text('file_url');
            $table->boolean('is_private')->nullable()->default(false);
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users');
            $table->timestamp('uploaded_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('file_name', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_type', 255)->nullable();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('cascade');

            $table->index('company_id', 'idx_files_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
