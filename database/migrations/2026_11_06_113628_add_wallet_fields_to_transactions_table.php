<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('wallet_id')->nullable()->after('user_id')->constrained('wallets')->nullOnDelete();
            $table->foreignId('destination_wallet_id')->nullable()->after('wallet_id')->constrained('wallets')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_id']);
            $table->dropForeign(['destination_wallet_id']);
            $table->dropColumn(['wallet_id', 'destination_wallet_id']);
        });
    }
};
