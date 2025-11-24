<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Paystack, Stripe, Razorpay, etc.
            $table->string('code')->unique(); // paystack, stripe, razorpay, payvibe, btcpay, coingate, manual
            $table->string('display_name'); // Display name for users
            $table->text('description')->nullable(); // Gateway description
            $table->string('icon')->nullable(); // Icon/emoji for display
            $table->boolean('is_active')->default(true); // Enable/disable gateway
            $table->boolean('is_enabled')->default(true); // Whether gateway is configured and working
            $table->integer('sort_order')->default(0); // Display order
            $table->json('config')->nullable(); // Gateway-specific configuration (API keys, etc.)
            $table->json('supported_currencies')->nullable(); // Supported currencies
            $table->decimal('min_amount', 10, 2)->nullable(); // Minimum transaction amount
            $table->decimal('max_amount', 10, 2)->nullable(); // Maximum transaction amount
            $table->decimal('fee_percentage', 5, 2)->default(0); // Fee percentage
            $table->decimal('fee_fixed', 10, 2)->default(0); // Fixed fee amount
            $table->text('instructions')->nullable(); // Instructions for users
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateways');
    }
};
