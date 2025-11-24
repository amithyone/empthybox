<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('credential_id')->nullable();
            $table->string('order_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, delivered, completed, replaced, refunded
            $table->string('payment_method')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('has_replacement_request')->default(false);
            $table->boolean('is_replaced')->default(false);
            $table->timestamps();
        });
        
        // Add foreign key constraint after product_credentials table exists
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('credential_id')->references('id')->on('product_credentials')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};






