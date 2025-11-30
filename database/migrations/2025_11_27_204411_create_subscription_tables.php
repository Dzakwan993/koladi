<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update tabel companies untuk trial
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('trial_start')->nullable()->after('deleted_at');
            $table->timestamp('trial_end')->nullable()->after('trial_start');
            $table->enum('status', ['trial', 'active', 'expired', 'canceled'])->default('trial')->after('trial_end');
        });

        // 2. Tabel plans (paket langganan)
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('plan_name', 100); // Paket A, B, C
            $table->decimal('price_monthly', 12, 2); // Harga per bulan
            $table->integer('base_user_limit'); // Batas user dasar (5, 10, 30)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabel addons
        Schema::create('addons', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('addon_name', 100); // "Tambahan User"
            $table->decimal('price_per_user', 12, 2); // Harga per user tambahan
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Tabel subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('company_id');
            $table->uuid('plan_id')->nullable();
            $table->integer('addons_user_count')->default(0); // Jumlah addon user
            $table->integer('total_user_limit')->default(0); // base_user_limit + addons
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['trial', 'active', 'expired', 'canceled', 'pending'])->default('trial');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('set null');
        });

        // 5. Tabel subscription_invoices
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('subscription_id');
            $table->string('external_id')->nullable(); // ID dari Midtrans
            $table->string('payment_url')->nullable(); // Link pembayaran Midtrans
            $table->decimal('amount', 12, 2); // Total bayar
            $table->string('billing_month', 20); // Format: "2025-11"
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('payment_details')->nullable(); // JSON response Midtrans
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });

        // Index untuk performa
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('company_id');
            $table->index('status');
        });

        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->index('subscription_id');
            $table->index('external_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('plans');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['trial_start', 'trial_end', 'status']);
        });
    }
};
