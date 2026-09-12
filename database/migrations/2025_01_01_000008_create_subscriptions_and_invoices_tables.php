<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->uuid('company_id');
            $table->uuid('plan_id')->nullable();
            $table->integer('addons_user_count')->default(0);
            $table->integer('total_user_limit')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['trial', 'active', 'expired', 'canceled', 'pending'])->default('trial');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')
                  ->references('id')
                  ->on('companies')
                  ->onDelete('cascade');

            $table->foreign('plan_id')
                  ->references('id')
                  ->on('plans')
                  ->onDelete('set null');

            $table->index('company_id');
            $table->index('status');
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('public.uuid_generate_v4()'));
            $table->uuid('subscription_id');
            $table->string('external_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('billing_month', 20);
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('payment_details')->nullable();
            $table->string('payment_method', 50)->default('midtrans');
            $table->string('proof_of_payment', 500)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_bank', 100)->nullable();
            $table->string('payer_account_number', 50)->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('subscriptions')
                  ->onDelete('cascade');

            $table->foreign('verified_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index('subscription_id');
            $table->index('external_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
    }
};
