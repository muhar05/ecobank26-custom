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
        Schema::table('waste_price_histories', function (Blueprint $table) {
            $table->string('unit', 20)->nullable()->after('new_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_price_histories', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
