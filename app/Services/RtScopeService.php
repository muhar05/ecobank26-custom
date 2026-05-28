<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * RtScopeService
 *
 * Reusable helper untuk menerapkan RT-based data scoping secara konsisten
 * di semua controller. Menghindari duplicate query scoping logic.
 *
 * Rules:
 * - admin_rw, bendahara_rw  → akses global (semua RT)
 * - admin_rt                → hanya melihat data RT sendiri
 * - User tanpa RT           → fallback ke global (prevent lockout)
 */
class RtScopeService
{
    /**
     * Apakah user memiliki akses global (tidak perlu RT scoping)?
     */
    public function isGlobal(User $user): bool
    {
        return $user->hasAnyRole(['admin_rw', 'bendahara_rw', 'bendahara']);
    }

    /**
     * Apakah user adalah admin_rt yang punya RT terdaftar?
     */
    public function isRtAdmin(User $user): bool
    {
        return $user->hasRole('admin_rt') && $user->rt_id !== null;
    }

    /**
     * Ambil rt_id dari user yang login.
     * Return null jika user tidak punya RT atau akses global.
     */
    public function getUserRtId(User $user): ?int
    {
        if ($this->isGlobal($user)) {
            return null;
        }

        return $user->rt_id;
    }

    /**
     * Terapkan RT scope ke Eloquent query untuk kolom rt_id fisik.
     *
     * Untuk entity: fund_categories, community_contributions, community_expenses
     *
     * - admin_rw/bendahara_rw → tidak filter
     * - admin_rt → filter where rt_id = user's rt_id (exclude NULL legacy data)
     *
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function applyRtScope(Builder $query, User $user): Builder
    {
        if ($this->isGlobal($user)) {
            return $query;
        }

        $rtId = $user->rt_id;

        if ($rtId === null) {
            // Admin RT tanpa RT terdaftar: tampilkan kosong untuk mencegah kebocoran data
            return $query->whereRaw('1 = 0');
        }

        // Admin RT: hanya data yang rt_id = RT mereka (exclude NULL legacy)
        return $query->where('rt_id', $rtId);
    }

    /**
     * Terapkan RT scope ke query via relasi KK (untuk bills, kks, members).
     *
     * - admin_rw/bendahara_rw → tidak filter
     * - admin_rt → filter whereHas('kk', rt_id = user's rt_id)
     *
     * @param Builder $query
     * @param User $user
     * @param string $kkRelation  Nama relasi ke Kk (default: 'kk')
     * @return Builder
     */
    public function applyKkRtScope(Builder $query, User $user, string $kkRelation = 'kk'): Builder
    {
        if ($this->isGlobal($user)) {
            return $query;
        }

        $rtId = $user->rt_id;

        if ($rtId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas($kkRelation, fn($q) => $q->where('rt_id', $rtId));
    }

    /**
     * Terapkan RT scope langsung ke tabel yang punya kolom rt_id (kks, members via kk).
     * Digunakan untuk query langsung pada tabel kks.
     *
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function applyDirectRtScope(Builder $query, User $user): Builder
    {
        if ($this->isGlobal($user)) {
            return $query;
        }

        $rtId = $user->rt_id;

        if ($rtId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('rt_id', $rtId);
    }

    /**
     * Scope untuk fund_categories: tampilkan global (NULL) + milik RT sendiri.
     * Berbeda dari contribution/expense — kategori global tetap visible ke RT.
     *
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function applyFundCategoryScope(Builder $query, User $user): Builder
    {
        if ($this->isGlobal($user)) {
            return $query;
        }

        $rtId = $user->rt_id;

        if ($rtId === null) {
            // Admin RT tanpa RT: hanya kategori global
            return $query->whereNull('rt_id');
        }

        // Admin RT: global (NULL) + milik RT mereka
        return $query->where(fn($q) => $q->whereNull('rt_id')->orWhere('rt_id', $rtId));
    }

    /**
     * Ambil metadata RT untuk disimpan ke activity_log JSON.
     */
    public function getRtMetadata(User $user): array
    {
        if ($user->rt_id === null) {
            return [];
        }

        return ['rt_id' => $user->rt_id];
    }
}
