<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->text('email')->nullable();
            $table->text('additional_info')->nullable(); // JSON for extra fields
            $table->boolean('is_sold')->default(false);
            $table->foreignId('sold_to_order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_credentials');
    }
};


