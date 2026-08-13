<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cleanup legacy Member / member_id.
     *
     * Dropped columns:
     *   - deposits.member_id
     *   - withdrawals.member_id
     *   - savings_ledgers.member_id
     *   - waste_customers.member_id
     *   - community_contributions.member_id (FK ke members yang masih tersisa dari modul RT/KK lama)
     *
     * Dropped table:
     *   - members
     *
     * Semua FK yang menunjuk ke `members` dilepas terlebih dahulu sebelum tabel di-drop.
     */
    public function up(): void
    {
        // Defensif: setiap drop dilakukan hanya jika kolom member_id benar-benar ada.
        // Aman untuk fresh install (kolom tidak pernah dibuat) dan untuk database
        // existing yang masih menyimpan kolom member_id dari migration lama.
        $dropMemberColumn = function (string $table): void {
            if (!Schema::hasColumn($table, 'member_id')) {
                return;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['member_id']);
                $table->dropColumn('member_id');
            });
        };

        // 1. deposits
        $dropMemberColumn('deposits');

        // 2. withdrawals
        $dropMemberColumn('withdrawals');

        // 3. savings_ledgers
        $dropMemberColumn('savings_ledgers');

        // 4. waste_customers
        $dropMemberColumn('waste_customers');

        // 5. community_contributions (sisa modul RT/KK) - wajib dilepas agar tabel `members` bisa di-drop
        if (Schema::hasTable('community_contributions')) {
            $dropMemberColumn('community_contributions');
        }

        // 6. members
        Schema::dropIfExists('members');
    }

    /**
     * Reverse the changes: recreate `members` and re-attach member_id columns.
     */
    public function down(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kk_id')->nullable()->constrained('kks')->nullOnDelete();
            $table->string('member_code')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->string('relationship', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
        });

        Schema::table('savings_ledgers', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
        });

        Schema::table('waste_customers', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
        });

        if (Schema::hasTable('community_contributions')) {
            Schema::table('community_contributions', function (Blueprint $table) {
                $table->foreignId('member_id')->nullable()->after('id')->constrained('members')->nullOnDelete();
            });
        }
    }
};
