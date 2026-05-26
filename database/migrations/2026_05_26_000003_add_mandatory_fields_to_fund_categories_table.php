<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_categories', function (Blueprint $table) {
            $table->boolean('is_mandatory')->default(false)->after('is_active');
            $table->decimal('monthly_amount', 15, 2)->default(0.00)->after('is_mandatory');
        });
    }

    public function down(): void
    {
        Schema::table('fund_categories', function (Blueprint $table) {
            $table->dropColumn(['is_mandatory', 'monthly_amount']);
        });
    }
};
