<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->uuid('workspace_id')->nullable()->after('color_id');
            $table->foreign('workspace_id')
                  ->references('id')
                  ->on('workspaces')
                  ->onDelete('cascade');
        });

        // Hapus data label lama yang tidak punya workspace (data bocor)
        DB::table('labels')->whereNull('workspace_id')->delete();

        // Enforce NOT NULL setelah data lama bersih
        Schema::table('labels', function (Blueprint $table) {
            $table->uuid('workspace_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropColumn('workspace_id');
        });
    }
};