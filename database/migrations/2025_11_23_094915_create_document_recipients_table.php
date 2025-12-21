<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_recipients', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('document_id');
        $table->uuid('user_id');
        $table->boolean('status')->default(true);
        $table->timestamps();

        $table->unique(['document_id','user_id']); // prevent duplicate
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_recipients');
    }
};
