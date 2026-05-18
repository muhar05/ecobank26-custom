<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waste_prices', function (Blueprint $table) {
            $table->decimal('member_price', 15, 2)->nullable()->after('collector_id');
            $table->decimal('collector_price', 15, 2)->nullable()->after('member_price');
        });

        // Backfill from price_per_unit
        DB::table('waste_prices')->whereNotNull('price_per_unit')->update([
            'member_price' => DB::raw('price_per_unit'),
            'collector_price' => DB::raw('price_per_unit'),
        ]);
    }

    public function down(): void
    {
        Schema::table('waste_prices', function (Blueprint $table) {
            $table->dropColumn(['member_price', 'collector_price']);
        });
    }
};
