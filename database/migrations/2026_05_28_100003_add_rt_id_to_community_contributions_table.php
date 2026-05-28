<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_contributions', function (Blueprint $table) {
            // NULL = legacy data, visible only to admin_rw/bendahara_rw
            // non-null = recorded by that RT's admin
            $table->foreignId('rt_id')
                ->nullable()
                ->after('fund_category_id')
                ->constrained('rts')
                ->onDelete('set null');

            $table->index('rt_id');
        });
    }

    public function down(): void
    {
        Schema::table('community_contributions', function (Blueprint $table) {
            $table->dropForeign(['rt_id']);
            $table->dropIndex(['rt_id']);
            $table->dropColumn('rt_id');
        });
    }
};
