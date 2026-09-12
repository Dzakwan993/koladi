<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->string('otp', 6);
            $table->string('type', 20);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index('email', 'otp_verifications_email_index');
        });

        DB::statement("ALTER TABLE public.otp_verifications ADD CONSTRAINT otp_verifications_type_check CHECK (((type)::text = ANY (ARRAY[('register'::character varying)::text, ('reset_password'::character varying)::text])))");
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
