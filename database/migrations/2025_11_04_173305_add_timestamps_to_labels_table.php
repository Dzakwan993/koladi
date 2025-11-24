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
        Schema::table('labels', function (Blueprint $table) {
            $table->timestamp('created_at')->default(now());
            $table->timestamp('updated_at')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};