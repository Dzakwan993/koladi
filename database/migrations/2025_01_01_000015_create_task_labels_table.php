<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_labels', function (Blueprint $table) {
            $table->foreignUuid('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignUuid('label_id')->constrained('labels')->onDelete('cascade');
            $table->dateTime('created_at', 0)->nullable();
            $table->dateTime('updated_at', 0)->nullable();

            $table->primary(['task_id', 'label_id']);
            $table->index('label_id', 'idx_task_labels_label');
            $table->index('task_id', 'idx_task_labels_task');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_labels');
    }
};
