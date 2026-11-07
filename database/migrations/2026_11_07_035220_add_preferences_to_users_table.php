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
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 10)->default('IDR')->after('email');
            $table->string('date_format', 20)->default('d M Y')->after('currency');
            $table->string('ledger_mode', 30)->default('strict_realtime')->after('date_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'date_format', 'ledger_mode']);
        });
    }
};
