<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->string('name', 255);
            $table->boolean('is_private')->nullable()->default(false);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            $table->uuid('parent_id')->nullable();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('cascade');

            $table->index('company_id', 'idx_folders_company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
