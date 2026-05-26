<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->string('rt_number', 10)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('kks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_id')->constrained('rts')->onDelete('cascade');
            $table->string('kk_number', 20)->nullable()->unique();
            $table->string('family_head', 100);
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('status', 30)->default('active'); // active, kontrak, pindah, kosong
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kks');
        Schema::dropIfExists('rts');
    }
};
