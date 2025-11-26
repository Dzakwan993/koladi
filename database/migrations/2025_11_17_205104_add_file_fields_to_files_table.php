<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();     // path dalam storage
            $table->integer('file_size')->nullable();
            $table->string('file_type')->nullable();     // extension
        });
    }

    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn([
                'file_name',
                'file_path',
                'file_size',
                'file_type',
            ]);
        });
    }
};
