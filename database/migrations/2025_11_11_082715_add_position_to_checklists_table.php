<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('is_done');
        });

        // Set position berdasarkan urutan created_at untuk data existing
        DB::statement('
            UPDATE checklists c1
            SET position = (
                SELECT COUNT(*)
                FROM checklists c2
                WHERE c2.task_id = c1.task_id
                AND c2.created_at <= c1.created_at
            ) - 1
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
