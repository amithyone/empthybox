<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('manual_payment_id')->nullable()->constrained('manual_payments')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->decimal('final_amount', 10, 2)->nullable(); // Amount after charges/fees
            $table->string('gateway'); // manual, paystack, stripe, razorpay, payvibe, etc.
            $table->string('reference')->unique();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('description')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['gateway', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposits');
    }
};
