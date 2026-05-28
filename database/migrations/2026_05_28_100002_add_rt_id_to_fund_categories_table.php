<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_categories', function (Blueprint $table) {
            // NULL = global/RW category, non-null = private to that RT
            $table->foreignId('rt_id')
                ->nullable()
                ->after('id')
                ->constrained('rts')
                ->onDelete('cascade');

            $table->index('rt_id');
        });
    }

    public function down(): void
    {
        Schema::table('fund_categories', function (Blueprint $table) {
            $table->dropForeign(['rt_id']);
            $table->dropIndex(['rt_id']);
            $table->dropColumn('rt_id');
        });
    }
};
