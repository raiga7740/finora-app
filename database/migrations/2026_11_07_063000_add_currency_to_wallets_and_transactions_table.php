<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('currency', 10)->default('IDR')->after('account_number');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('currency', 10)->nullable()->after('amount');
            $table->decimal('destination_amount', 15, 2)->nullable()->after('currency');
            $table->decimal('exchange_rate', 15, 6)->nullable()->after('destination_amount');
            $table->decimal('base_amount', 15, 2)->nullable()->after('exchange_rate');
        });

        // Backfill existing wallets with their owner's currency
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("
                UPDATE wallets
                SET currency = (SELECT COALESCE(currency, 'IDR') FROM users WHERE users.id = wallets.user_id)
                WHERE EXISTS (SELECT 1 FROM users WHERE users.id = wallets.user_id)
            ");
        } else {
            DB::statement("
                UPDATE wallets w
                INNER JOIN users u ON w.user_id = u.id
                SET w.currency = COALESCE(u.currency, 'IDR')
            ");
        }

        // Backfill existing transactions base_amount with amount
        DB::statement("
            UPDATE transactions
            SET base_amount = amount
            WHERE base_amount IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['currency']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['currency', 'destination_amount', 'exchange_rate', 'base_amount']);
        });
    }
};
