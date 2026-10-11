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
        Schema::table('deposits', function (Blueprint $table) {
            $table->index('date', 'deposits_date_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('date', 'sales_date_index');
        });

        Schema::table('waste_bank_cash_ledgers', function (Blueprint $table) {
            $table->index('date', 'waste_bank_cash_ledgers_date_index');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->index('date', 'withdrawals_date_index');
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->index(['waste_customer_id', 'type'], 'savings_ledgers_customer_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex('deposits_date_index');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_date_index');
        });

        Schema::table('waste_bank_cash_ledgers', function (Blueprint $table) {
            $table->dropIndex('waste_bank_cash_ledgers_date_index');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropIndex('withdrawals_date_index');
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->dropIndex('savings_ledgers_customer_type_index');
        });
    }
};
