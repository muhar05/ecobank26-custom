<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable(false)->change();
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable(false)->change();
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable(false)->change();
        });
    }
};
