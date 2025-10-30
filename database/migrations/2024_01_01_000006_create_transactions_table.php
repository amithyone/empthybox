<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // deposit, withdrawal, purchase, refund
            $table->decimal('amount', 10, 2);
            $table->string('gateway')->nullable(); // paystack, stripe, razorpay, etc.
            $table->string('reference')->unique();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('description')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};






