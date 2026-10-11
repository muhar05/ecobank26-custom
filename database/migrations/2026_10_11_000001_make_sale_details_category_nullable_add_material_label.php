<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historical exception support for sale details (matching deposit details).
 *
 * Mengizinkan baris penjualan komposit historis (misal Buku, Majalah, Brosur, Koran
 * dan Seng/Kawat) menyimpan raw label di material_label dengan waste_category_id NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->string('material_label')->nullable()->after('waste_category_id');
        });

        $mysql = Schema::getConnection()->getDriverName() === 'mysql';

        if ($mysql) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->dropForeign(['waste_category_id']);
            });
        }

        Schema::table('sale_details', function (Blueprint $table) {
            $table->unsignedBigInteger('waste_category_id')->nullable()->change();
        });

        if ($mysql) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->foreign('waste_category_id')->references('id')->on('waste_categories');
            });
        }
    }

    public function down(): void
    {
        $mysql = Schema::getConnection()->getDriverName() === 'mysql';

        if ($mysql) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->dropForeign(['waste_category_id']);
            });
        }

        Schema::table('sale_details', function (Blueprint $table) {
            $table->unsignedBigInteger('waste_category_id')->nullable(false)->change();
            $table->dropColumn('material_label');
        });

        if ($mysql) {
            Schema::table('sale_details', function (Blueprint $table) {
                $table->foreign('waste_category_id')->references('id')->on('waste_categories');
            });
        }
    }
};
