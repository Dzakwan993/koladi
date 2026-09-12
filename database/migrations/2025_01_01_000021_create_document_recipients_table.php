<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->uuid('document_id');
            $table->uuid('user_id');
            $table->boolean('status')->default(true);
            $table->dateTime('created_at', 0)->nullable();
            $table->dateTime('updated_at', 0)->nullable();

            $table->unique(['document_id', 'user_id'], 'document_recipients_document_id_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_recipients');
    }
};
