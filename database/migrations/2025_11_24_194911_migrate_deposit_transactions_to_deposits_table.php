<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Migrate existing deposit transactions to deposits table
        DB::statement("
            INSERT INTO deposits (
                user_id,
                wallet_id,
                transaction_id,
                amount,
                final_amount,
                gateway,
                reference,
                status,
                description,
                gateway_response,
                completed_at,
                created_at,
                updated_at
            )
            SELECT 
                user_id,
                wallet_id,
                id as transaction_id,
                amount,
                final_amount,
                COALESCE(gateway, 'manual') as gateway,
                reference,
                status,
                description,
                gateway_response,
                CASE WHEN status = 'completed' THEN updated_at ELSE NULL END as completed_at,
                created_at,
                updated_at
            FROM transactions
            WHERE type = 'deposit'
            AND NOT EXISTS (
                SELECT 1 FROM deposits WHERE deposits.transaction_id = transactions.id
            )
        ");
    }

    public function down()
    {
        // This migration is not reversible as we're moving data
        // If needed, deposits can be recreated from transactions table
    }
};
