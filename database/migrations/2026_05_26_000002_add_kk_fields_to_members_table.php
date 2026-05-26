<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('kk_id')->nullable()->after('user_id')->constrained('kks')->onDelete('set null');
            $table->string('relationship', 50)->nullable()->after('address'); // e.g. Kepala Keluarga, Istri, Anak
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['kk_id']);
            $table->dropColumn(['kk_id', 'relationship']);
        });
    }
};
