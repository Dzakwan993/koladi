<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID sebagai PK
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->timestamps();
            $table->softDeletes(); // untuk deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
