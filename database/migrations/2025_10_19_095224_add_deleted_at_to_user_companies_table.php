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
        Schema::table('user_companies', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada, jika belum baru tambahkan
            if (!Schema::hasColumn('user_companies', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};