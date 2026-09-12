<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('workspace_id')->nullable()->constrained('workspaces')->onDelete('cascade');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('end_datetime')->nullable();
            $table->string('recurrence', 100)->nullable();
            $table->boolean('is_private')->nullable()->default(false);
            $table->boolean('is_online_meeting')->nullable()->default(false);
            $table->text('meeting_link')->nullable();
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('location', 255)->nullable();
        });

        Schema::create('calendar_participants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->foreignUuid('event_id')->nullable()->constrained('calendar_events')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('status', 50)->nullable();
            $table->boolean('attendance')->nullable()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_participants');
        Schema::dropIfExists('calendar_events');
    }
};
