<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_credentials', function (Blueprint $table) {
            $table->string('authenticator_code')->nullable()->after('email');
            $table->string('authenticator_site')->nullable()->after('authenticator_code');
        });
    }

    public function down(): void
    {
        Schema::table('product_credentials', function (Blueprint $table) {
            $table->dropColumn(['authenticator_code', 'authenticator_site']);
        });
    }
};


