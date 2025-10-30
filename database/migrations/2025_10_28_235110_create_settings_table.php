<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, json, number
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'description' => 'Enable/Disable maintenance mode', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_message', 'value' => 'We are currently performing scheduled maintenance. Please check back soon.', 'type' => 'string', 'description' => 'Maintenance mode message', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_name', 'value' => 'BiggestLogs', 'type' => 'string', 'description' => 'Site name', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_email', 'value' => 'admin@biggestlogs.com', 'type' => 'string', 'description' => 'Site email', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'manual_payment_enabled', 'value' => '1', 'type' => 'boolean', 'description' => 'Enable manual payment processing', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'manual_payment_instructions', 'value' => 'Contact admin for manual payment instructions', 'type' => 'string', 'description' => 'Manual payment instructions', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
