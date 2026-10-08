<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historical exception support (Opsi A).
 *
 * Deposit detail historis boleh TIDAK punya WasteCategory (tidak dipaksa jadi
 * canonical), cukup menyimpan raw label di `material_label`. Import
 * operasional tetap WAJIB memakai WasteCategory (dijaga di service).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposit_details', function (Blueprint $table) {
            $table->string('material_label')->nullable()->after('waste_category_id');
        });

        $mysql = Schema::getConnection()->getDriverName() === 'mysql';

        if ($mysql) {
            Schema::table('deposit_details', function (Blueprint $table) {
                $table->dropForeign(['waste_category_id']);
            });
        }

        Schema::table('deposit_details', function (Blueprint $table) {
            $table->unsignedBigInteger('waste_category_id')->nullable()->change();
        });

        if ($mysql) {
            Schema::table('deposit_details', function (Blueprint $table) {
                $table->foreign('waste_category_id')->references('id')->on('waste_categories');
            });
        }
    }

    public function down(): void
    {
        // Baris historical exception tidak punya kategori; hapus agar bisa
        // dikembalikan ke NOT NULL.
        DB::table('deposit_details')->whereNull('waste_category_id')->delete();

        $mysql = Schema::getConnection()->getDriverName() === 'mysql';

        if ($mysql) {
            Schema::table('deposit_details', function (Blueprint $table) {
                $table->dropForeign(['waste_category_id']);
            });
        }

        Schema::table('deposit_details', function (Blueprint $table) {
            $table->unsignedBigInteger('waste_category_id')->nullable(false)->change();
            $table->dropColumn('material_label');
        });

        if ($mysql) {
            Schema::table('deposit_details', function (Blueprint $table) {
                $table->foreign('waste_category_id')->references('id')->on('waste_categories');
            });
        }
    }
};
