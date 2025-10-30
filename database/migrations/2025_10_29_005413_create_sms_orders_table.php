<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider_order_id')->nullable(); // Order ID from SMSPool
            $table->string('service_id');
            $table->string('service_name')->nullable();
            $table->string('country_id')->nullable();
            $table->string('country_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'expired', 'cancelled'])->default('pending');
            $table->text('sms_code')->nullable(); // The received SMS code
            $table->text('sms_text')->nullable(); // Full SMS text
            $table->timestamp('sms_received_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_orders');
    }
};
