<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('waste_customer_id')->nullable()->after('member_id')->constrained('waste_customers')->nullOnDelete();
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('waste_customer_id')->nullable()->after('member_id')->constrained('waste_customers')->nullOnDelete();
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->foreignId('waste_customer_id')->nullable()->after('member_id')->constrained('waste_customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropForeign(['waste_customer_id']);
            $table->dropColumn('waste_customer_id');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign(['waste_customer_id']);
            $table->dropColumn('waste_customer_id');
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->dropForeign(['waste_customer_id']);
            $table->dropColumn('waste_customer_id');
        });
    }
};
