<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('announcements', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('workspace_id')->nullable()->constrained()->onDelete('cascade');
        $table->uuid('created_by');
        $table->string('title', 255);
        $table->text('description');
        $table->date('due_date');
        $table->date('auto_due')->nullable();
        $table->boolean('is_private')->default(false);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
