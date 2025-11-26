<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Hapus tabel lama jika ada
        Schema::dropIfExists('task_labels');

        // Buat tabel baru dengan struktur yang benar
        Schema::create('task_labels', function (Blueprint $table) {
            $table->uuid('task_id');
            $table->uuid('label_id');
            $table->timestamps();

            $table->primary(['task_id', 'label_id']);
            
            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
                  
            $table->foreign('label_id')
                  ->references('id')
                  ->on('labels')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('task_labels');
        
        // Jika ingin mengembalikan ke struktur lama, buat kembali dengan kolom id
        Schema::create('task_labels', function (Blueprint $table) {
            $table->id();
            $table->uuid('task_id');
            $table->uuid('label_id');
            $table->timestamps();

            $table->foreign('task_id')
                  ->references('id')
                  ->on('tasks')
                  ->onDelete('cascade');
                  
            $table->foreign('label_id')
                  ->references('id')
                  ->on('labels')
                  ->onDelete('cascade');
        });
    }
};