<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->string('full_name', 255);
            $table->string('email', 255)->unique();
            $table->text('password');
            $table->string('google_id', 255)->nullable();
            $table->boolean('status_active')->default(true);
            $table->uuid('system_role_id')->nullable();
            $table->string('avatar', 500)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('onboarding_step', 255)->nullable();
            $table->boolean('has_seen_onboarding')->default(false);
            $table->string('onboarding_type', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('system_role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
