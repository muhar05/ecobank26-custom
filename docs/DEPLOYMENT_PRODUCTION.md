# SOP Deployment & Migration Production — Ecobank026 Custom

Dokumen ini adalah panduan operasional (SOP) untuk melakukan **deployment** dan **migration database** pada lingkungan **production** (Hostinger, domain `ecobank026.my.id`). Dokumentasi ini didasarkan pada kondisi aktual project, bukan template generik.

> **⚠️ WARNING UTAMA**
> Jangan pernah menjalankan `php artisan migrate:fresh` pada database production.
> `migrate:fresh` akan **menghapus semua tabel beserta datanya** dan menjalankan ulang seluruh migration dari nol. Tidak ada cara aman untuk memulihkan data tanpa backup.
> Selalu buat **backup database** sebelum operasi apa pun.

---

## 0. Ringkasan Kondisi Aktual Project (Hasil Audit)

| Aspek | Kondisi |
|-------|---------|
| Framework | Laravel 13 (`laravel/framework: ^13.8`) |
| Requirement PHP | `php: ^8.3` (composer.json) |
| PHP Production (Hostinger) | 8.4 (path: `/opt/alt/php84/usr/bin/php`) |
| PHP Local | 8.5 |
| Database Production | MySQL |
| Database Local (.env) | MySQL (`.env` sudah `DB_CONNECTION=mysql`) |
| Queue / Session / Cache | `database` (default) |
| Asset build | Vite (`npm run build`) |
| Branch production (saat ini) | `main` (berisi RT/RW + Bank Sampah) |
| Branch development (saat ini) | `refactor-v2` (Bank Sampah saja) |
| Workflow deploy | `.github/workflows/deploy.yml` (push ke `main`) |
| Folder produksi | `domains/ecobank026.my.id/public_html` |

Fitur utama aplikasi: **Bank Sampah** (nasabah, setoran, penarikan, harga sampah, penjualan, buku kas) dan (pada `main`) **Kas RT/RW** (RT, RW, KK, iuran, tagihan).

### Komponen yang perlu diperhatikan saat deploy
- **Storage / upload**: `storage/app/public` disimbolkan ke `public/storage`. Data nasabah, bukti/berkas, dan export ada di sini.
- **Export Excel**: project memakai `maatwebsite/excel`.
- **Permission/Role**: project memakai `spatie/laravel-permission` (tabel `permissions`, `roles`, `model_has_*`).
- **Kode baru pada `refactor-v2`**: menambahkan fitur *Balance Check* nasabah dan menghapus ketergantungan modul RT/RW & `Member`.

---

## 1. DEPLOY KE HOSTING YANG SUDAH MENJALANKAN PROJECT

Kasus ini berlaku ketika **project sudah live**, user sudah memakai aplikasi, database production sudah berisi data, dan ada perubahan kode (dari branch development) serta kemungkinan ada perubahan migration/schema.

> Tujuan: menambah kode baru **tanpa kehilangan data user**.

### 1.1 Workflow aman (urutan)

1. **Backup database production**
   - Ekspor lewat phpMyAdmin hPanel, atau melalui SSH dengan `mysqldump`.
   - Simpan hasil backup di luar folder project (mis. komputer lokal + satu salinan lain).
2. **Backup code / config penting**
   - `.env` (berisi credential database, APP_KEY, dsb.) — ini TIDAK boleh hilang.
   - `storage/app/public` jika ada file upload/export yang belum ikut git.
3. **Cek git branch production**
   - Pastikan branch yang di-deploy sesuai dengan target.
   - Production saat ini memakai `main`. Jangan asal `git checkout refactor-v2` di server tanpa proses yang jelas (lihat bagian 4).
4. **Deploy code**
   - Tarik kode terbaru dari git di folder `public_html`:
     `git pull origin <branch>`
   - Atau jalankan workflow GitHub Actions `deploy.yml` (push ke branch yang dikonfigurasi).
5. **Jalankan migration jika diperlukan** (hanya jika ada file migration baru)
   - `php artisan migrate --force`
   - `--force` wajib karena production tidak dalam mode `APP_ENV=local`.
6. **Clear / cache optimize**
   - `php artisan optimize:clear` lalu `php artisan optimize`
   - `php artisan storage:link` jika link belum ada / file storage belum terhubung.
   - Build ulang asset jika ada perubahan frontend: `npm install --ignore-scripts` + `npm run build`.
7. **Smoke test**
   - Login sebagai admin, buka halaman utama, cek beberapa fitur inti (setoran, penarikan, laporan).
8. **Rollback jika deployment gagal**
   - Lihat bagian 5 (Rollback).

### 1.2 Aturan penting saat migration di production

- **⚠️ JANGAN pakai `migrate:fresh`** — ini menghapus semua data.
- **Jangan menghapus / mereset data user.** Migration boleh mengubah struktur, tetapi tidak boleh melakukan `DELETE`, `TRUNCATE`, atau `DROP` pada tabel yang masih berisi data penting tanpa backup + persetujuan.
- **Migration production harus backward-safe** jika memungkinkan. Artinya: migration baru boleh menambah kolom/tabel, tetapi sebaiknya tidak langsung menghapus kolom/tabel yang masih dipakai kode lama, agar bisa di-rollback.
- **Migration lama yang sudah pernah dijalankan tidak boleh diedit sembarangan.** File migration yang sudah tercatat di tabel `migrations` production tidak boleh diubah isinya. Jika perlu perubahan, buat **file migration baru**, bukan mengedit yang lama.
- **Kapan membuat migration baru:** setiap kali ada perubahan struktur database (tambah kolom, tabel, index, ubah tipe kolom). Tidak pernah mengedit migration yang sudah berjalan.
- **Cara menangani migration yang sudah terlanjur dijalankan di production:**
  - Jangan edit filenya. Buat migration baru yang memperbaiki/menyesuaikan schema.
  - Jika memang harus menghapus file migration yang belum pernah dijalankan, pastikan dulu dengan `migrate:status` bahwa file tersebut **belum** terdaftar di tabel `migrations` production.
- **Cara cek `php artisan migrate:status`:**
  - Menampilkan daftar file migration dan statusnya (`Ran` / `Pending`).
  - Migration bertanda `Ran` sudah dieksekusi → jangan diedit. Migration bertanda `Pending` belum dieksekusi → akan dijalankan saat `migrate --force`.

---

## 2. PINDAH HOSTING / SERVER

Kasus ini berlaku ketika project **dipindahkan dari hosting lama ke hosting baru** (misalnya pindah server Hostinger, atau pindah ke VPS/penyedia lain). Database dan file production harus ikut dipindah, dan user tetap bisa memakai aplikasi.

> Tujuan: memindahkan **seluruh aplikasi + data** ke server baru tanpa kehilangan apa pun dan tanpa downtime lama.

### 2.1 Langkah-langkah

1. **Backup database production** (server lama)
   - `mysqldump` seluruh database, atau ekspor dari phpMyAdmin.
2. **Export / import database**
   - Buat database kosong di server baru dengan collation/encoding sama (biasanya `utf8mb4`).
   - Import file backup ke database baru.
   - Catat nama user database, password, host, dan port database baru (jangan pernah dibagikan / ditulis di dokumentasi ini).
3. **Backup storage / uploads** (jika ada)
   - Salin seluruh isi `storage/app/public` dari server lama ke server baru.
4. **Setup PHP version yang sesuai**
   - Laravel 13 butuh PHP ≥ 8.3. Gunakan PHP 8.4 (sama seperti produksi sekarang) untuk konsistensi.
5. **Setup extensions PHP**
   - Pastikan extension yang dibutuhkan tersedia: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `gd` (untuk Excel/import), `fileinfo`, `openssl`, `intl`, `tokenizer`, `ctype`, `json`, `bcmath`.
6. **Setup Composer**
   - Install Composer di server baru.
   - Jalankan `composer install --no-dev --optimize-autoloader` (jangan `composer update` agar versi package sesuai `composer.lock`).
7. **Setup Node / build assets** (jika diperlukan)
   - Install Node.js dan npm, lalu `npm install --ignore-scripts` dan `npm run build` untuk menghasilkan asset di `public/build`.
8. **Setup `.env`**
   - Salin `.env` dari server lama, atau buat ulang dari `.env.example`.
   - **JANGAN pernah menampilkan/menulis isi secret** (APP_KEY, DB_PASSWORD, dsb.). APP_KEY harus sama dengan server lama, jika tidak semua data terenkripsi (session, dsb.) akan gagal dibaca.
   - Atur `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, dan konfigurasi database baru.
9. **Konfigurasi database baru**
   - Isi `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di `.env` sesuai database baru.
10. **Konfigurasi document root**
    - Arahkan document root web server ke folder `public` Laravel (mis. `domains/domain.id/public_html/public` atau konfigurasi vhost). Jangan arahkan ke root project, karena file seperti `.env` tidak boleh terekspos.
11. **Konfigurasi storage / link**
    - Jalankan `php artisan storage:link` agar `public/storage` menunjuk ke `storage/app/public`.
12. **Setup Git / deployment**
    - Inisialisasi git / clone repo di server baru, set remote, dan pastikan branch production (saat ini `main`) siap ditarik.
13. **Jalankan migration hanya jika diperlukan**
    - Jika database yang dipindah sudah berisi seluruh tabel, **tidak perlu** menjalankan migration. Jalankan `php artisan migrate --force` hanya jika ada migration `Pending` (periksa dengan `migrate:status`).
    - ⚠️ Jangan `migrate:fresh` — database sudah berisi data.
14. **Permission folder**
    - `storage/` dan `bootstrap/cache/` harus bisa ditulis web server: `chmod -R 775 storage bootstrap/cache` (atau sesuai standar user hosting).
    - Folder lain sebaiknya `644` untuk file dan `755` untuk direktori.
15. **Cache / config clear**
    - `php artisan optimize:clear` lalu `php artisan optimize`.
16. **Testing sebelum DNS / domain diarahkan**
    - Uji aplikasi lewat alamat sementara / IP server baru (mis. edit file `hosts` lokal) sebelum memutus DNS.
    - Pastikan login, dashboard, setoran, penarikan, laporan, dan export bekerja.
17. **Update DNS**
    - Arahkan record DNS domain ke server baru (A record, CNAME, dsb. sesuai penyedia).
    - Tunggu propagasi DNS.
18. **Final smoke test**
    - Setelah DNS jalan, uji lewat domain resmi dari perangkat berbeda.

> **⚠️ WARNING:** Jika `.env` APP_KEY di server baru berbeda dengan server lama, session login user akan ter-reset dan data terenkripsi tidak terbaca. Selalu pakai APP_KEY lama.

---

## 3. DATABASE PRODUCTION SUDAH DIPAKAI, LALU SCHEMA BERUBAH

Kasus nyata berdasarkan struktur project ini:

**Kondisi awal production (skema lama, masih `main`):**
Production sudah memiliki data nasabah (tabel `waste_customers`), setoran (`deposits`), penarikan (`withdrawals`), buku kas nasabah (`savings_ledgers`), plus modul lama RT/RW (`rts`, `kks`, `members`, `community_contributions`, `bills`, `fund_categories`, dst.) dan kolom `member_id` / `rt_id` yang mengikat.

**Kondisi development (`refactor-v2`):**
Developer menghapus modul RT/RW dan `Member`, mengganti relasi nasabah dari `member_id` menjadi `waste_customer_id`. Kolom/tabel lama tidak lagi digunakan oleh kode baru.

**Perubahan schema pada `refactor-v2` (migration baru):**
- `2026_08_13_000000_remove_member_id_and_drop_members_table.php` — menghapus kolom `member_id` dari `deposits`, `withdrawals`, `savings_ledgers`, `waste_customers`, `community_contributions`, lalu men-drop tabel `members`.
- `2026_08_13_000001_remove_legacy_rt_rw_tables.php` — men-drop tabel legacy RT/RW (`bill_payments`, `community_expenses`, `community_cash_ledgers`, `bills`, `community_contributions`, `fund_categories`, `kks`, `rts`) dan menghapus kolom `users.rt_id`.

> **⚠️ WARNING:** Migration ini bersifat **destruktif** (DROP tabel/kolom). Data modul RT/RW dan data `Member` akan **hilang**. Pastikan memang tidak dibutuhkan lagi, dan sudah ada **backup** sebelum menjalankan.

### 3.1 Workflow aman untuk perubahan schema

1. **Audit schema production terlebih dahulu**
   - Cek `php artisan migrate:status` untuk mengetahui migration mana yang sudah `Ran`.
   - Cek struktur aktual: pastikan tabel/kolom yang akan di-drop benar-benar ada, dan tidak ada kode lain yang masih memakainya.
2. **Backup database**
   - Backup penuh database production sebelum apa pun.
3. **Buat migration baru**
   - Jika belum ada, tulis migration baru untuk perubahan schema. Jangan mengedit migration lama yang sudah `Ran`.
4. **Test migration pada database clone / staging**
   - Clone database production ke lokal/staging, jalankan migration di sana, pastikan sukses dan data nasabah tetap utuh.
5. **Cek data sebelum migration**
   - Catat jumlah record penting: jumlah `waste_customers`, `deposits`, `withdrawals`, `savings_ledgers`, dan saldo/ledger.
6. **Jalankan migration di production dengan `--force`**
   - `php artisan migrate --force`
   - Lakukan di jam sepi / maintenance singkat.
7. **Verifikasi schema**
   - Pastikan tabel/kolom sudah sesuai skema baru (tabel lama hilang, tabel nasabah baru ada).
8. **Verifikasi jumlah record**
   - Bandingkan jumlah record setelah migration dengan catatan sebelum migration.
9. **Verifikasi saldo / ledger**
   - Cek total saldo nasabah dan `savings_ledgers` tetap konsisten (tidak berkurang/terduplikasi).
10. **Jalankan smoke test fitur utama**
    - Login, setoran baru, penarikan, laporan, export Excel.
11. **Jangan melakukan destructive operation tanpa backup dan approval**
    - Semua operasi DROP/DELETE harus melalui persetujuan dan didahului backup yang tervalidasi (bisa di-restore).

### 3.2 Contoh workflow end-to-end

```
Development  →  Staging/Test DB  →  Backup Production  →  Migration Production  →  Deploy Code  →  Verification
```

1. **Development:** tulis migration + kode baru di `refactor-v2`, jalankan & uji lokal.
2. **Staging/Test DB:** clone database production, jalankan migration di sana, uji fitur & data.
3. **Backup Production:** backup penuh database (dan storage) production.
4. **Migration Production:** jalankan `php artisan migrate --force`.
5. **Deploy Code:** tarik kode `refactor-v2` / build asset / clear cache.
6. **Verification:** cek schema, jumlah record, saldo/ledger, dan smoke test.

> Urutan **migration sebelum deploy code** (atau bersamaan) penting agar saat kode baru aktif, schema sudah siap.

---

## 4. KHUSUS PROJECT INI — Pindah Production dari `main` ke `refactor-v2`

Kondisi saat ini:
- **Production** masih menggunakan branch **`main`** (RT/RW + Bank Sampah).
- **Development** sudah menggunakan **`refactor-v2`** (Bank Sampah saja).
- **Database production** masih berisi skema lama (RT/RW + `members` + `member_id`).
- **`deploy.yml`** GitHub Actions saat ini melakukan deploy dari branch `main` (`git pull origin main`).

Developer ingin memindahkan production ke `refactor-v2`. Karena ini melibatkan **perubahan schema destruktif** dan **penghapusan modul RT/RW**, urutan harus sangat hati-hati.

### 4.1 Risiko utama

- Migration `refactor-v2` akan **menghapus data RT/RW & Member** dari database production.
- `deploy.yml` masih men-deploy `main`. Jika belum diubah, kode `main` dan migration `refactor-v2` bisa bentrok.
- User yang masih mengandalkan fitur RT/RW akan kehilangan aksesnya.

### 4.2 Urutan aman sebelum deployment

1. **Komunikasi & persetujuan**
   - Pastikan semua pihak setuju memindahkan production ke `refactor-v2` dan memahami bahwa modul RT/RW & data Member akan dihapus.
2. **Audit & bandingkan skema**
   - `php artisan migrate:status` di production (branch `main`).
   - Catat migration yang sudah `Ran` di production dan daftar migration `refactor-v2` (perhatian pada `2026_08_13_000000` dan `2026_08_13_000001`).
3. **Backup lengkap**
   - Backup penuh database **dan** `storage/app/public` production.
   - Simpan backup di luar server.
4. **Persiapkan file migration yang benar di server**
   - Cabut kode `refactor-v2` (berisi seluruh migration `refactor-v2`). Pastikan `composer.lock` dan `package-lock.json` ikut, lalu `composer install --no-dev --optimize-autoloader`.
5. **Siapkan branch deploy / ubah `deploy.yml` (jika diperlukan)**
   - ⚠️ Perhatian: `deploy.yml` saat ini men-deploy `main`. Jika ingin `refactor-v2` menjadi target produksi, workflow perlu diubah agar men-deploy `refactor-v2` (mis. ubah branch trigger & `git pull origin refactor-v2`). Ini perlu disepakati sebelum go-live agar tidak men-deploy kode lama ke server yang sudah migrasi.
6. **Uji migration di staging dulu**
   - Clone database production, jalankan migration `refactor-v2`, pastikan sukses & data nasabah utuh.
7. **Jalankan migration production**
   - `php artisan migrate --force` (setelah backup valid).
   - ⚠️ Ini akan menghapus tabel RT/RW & `members`.
8. **Deploy kode `refactor-v2` + build + cache**
   - Tarik kode, `composer install --no-dev`, `npm install --ignore-scripts`, `npm run build`, `php artisan optimize:clear`, `php artisan optimize`, `php artisan storage:link`.
9. **Verifikasi (sesuai bagian 3.1)**
   - Cek schema, jumlah record nasabah, saldo/ledger, smoke test fitur Bank Sampah.
10. **Monitoring**
    - Pantau log (`storage/logs/laravel.log`) beberapa hari pertama.

> **⚠️ WARNING:** Karena migration `refactor-v2` bersifat destruktif terhadap data RT/RW & Member, pertimbangkan mengekspor/arsip data tersebut terlebih dahulu jika suatu saat masih diperlukan (misal untuk laporan). Setelah di-drop, data tidak bisa dipulihkan kecuali dari backup.

---

## 5. ROLLBACK

Rollback dipakai saat deployment atau migration menyebabkan masalah. **Pahami kapan cukup rollback code dan kapan harus restore database.**

### 5.1 Rollback code
- Tarik kembali commit/branch sebelumnya:
  `git checkout <commit-sebelumnya>` atau `git revert`.
- Rebuild asset & clear cache: `npm run build`, `php artisan optimize:clear`.
- **Kapan cukup rollback code saja:**
  - Deployment gagal karena error kode (blade, controller, route) **tanpa** ada perubahan schema.
  - Migration belum dijalankan (schema tidak berubah) → cukup balik kodenya.
  - Migration sudah dijalankan tapi **backward-safe** dan bisa di-`rollback` migration-nya.

### 5.2 Rollback migration (jika memungkinkan)
- `php artisan migrate:rollback --step=1 --force` menjalankan metode `down()` pada migration terakhir.
- **⚠️ WARNING — jangan sembarangan:**
  - `migrate:rollback` **tidak selalu** mengembalikan data yang dihapus. Jika `up()` menghapus kolom/tabel berisi data, `down()` hanya membuat ulang struktur **kosong** — data tetap hilang.
  - Jangan jalankan `migrate:rollback` tanpa backup dan tanpa memahami isi metode `down()` setiap migration.
  - Untuk migration destruktif (`2026_08_13_000000`, `2026_08_13_000001`), rollback hanya mengembalikan struktur kosong, bukan data.

### 5.3 Restore database backup
- Jika data hilang/rusak dan rollback tidak bisa mengembalikannya, **restore dari backup**: import file `mysqldump` ke database.
- **Kapan harus restore database:**
  - Ada data yang terhapus karena migration destruktif.
  - Migration berhasil `Ran` tapi salah / menyebabkan inkonsistensi data.
  - Rollback migration tidak memulihkan data.

### 5.4 Keputusan singkat
| Situasi | Tindakan |
|---------|----------|
| Error kode, schema tidak berubah | Rollback code saja |
| Schema berubah, migration backward-safe | Rollback code + `migrate:rollback` |
| Data terhapus / schema destruktif | Restore database backup (jangan andalkan `migrate:rollback`) |

> **Aturan:** selalu punya backup yang tervalidasi sebelum operasi apa pun. Ketika ragu antara rollback migration vs restore database, pilih **restore database** dari backup terbaru jika data penting.

---

## 6. CHECKLIST

### A. Normal deployment
- [ ] Backup database production
- [ ] Cek `git status` & branch production benar
- [ ] `git pull origin <branch>` / jalankan workflow deploy
- [ ] Cek `php artisan migrate:status` → apakah ada `Pending`
- [ ] (Jika ada migration) `php artisan migrate --force`
- [ ] (Jika ada perubahan asset) `npm install --ignore-scripts` + `npm run build`
- [ ] `php artisan optimize:clear`
- [ ] `php artisan optimize`
- [ ] `php artisan storage:link` (jika perlu)
- [ ] Smoke test fitur utama
- [ ] Rollback plan disiapkan

### B. Database migration production
- [ ] Audit schema (`migrate:status`)
- [ ] Backup database (validasi bisa di-restore)
- [ ] Uji migration di database clone/staging
- [ ] Catat jumlah record & saldo/ledger sebelum migrasi
- [ ] `php artisan migrate --force`
- [ ] Verifikasi schema
- [ ] Verifikasi jumlah record
- [ ] Verifikasi saldo/ledger
- [ ] Smoke test fitur utama
- [ ] Tidak memakai `migrate:fresh`

### C. Pindah hosting
- [ ] Backup database production
- [ ] Backup `storage/app/public`
- [ ] Export & import database ke server baru
- [ ] Setup PHP versi & extensions yang sesuai
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm install --ignore-scripts` + `npm run build`
- [ ] Setup `.env` (APP_KEY sama dengan lama, tanpa membocorkan secret)
- [ ] Arahkan document root ke folder `public`
- [ ] `php artisan storage:link`
- [ ] Setup git/deployment
- [ ] Migration hanya jika ada `Pending`
- [ ] Permission folder `storage` & `bootstrap/cache`
- [ ] `php artisan optimize:clear` + `php artisan optimize`
- [ ] Uji via alamat sementara sebelum ganti DNS
- [ ] Update DNS
- [ ] Final smoke test via domain resmi

### D. Rollback
- [ ] Identifikasi jenis kegagalan (code saja vs schema/data)
- [ ] (Code saja) checkout commit sebelumnya + rebuild asset + clear cache
- [ ] (Schema backward-safe) `php artisan migrate:rollback --step=1 --force`
- [ ] (Data hilang) restore database dari backup terbaru
- [ ] Verifikasi aplikasi kembali normal
- [ ] Catat penyebab & langkah perbaikan

---

## 7. COMMAND REFERENCE

Semua command dijalankan di dalam folder project (`domains/ecobank026.my.id/public_html` pada production).

### Git & status
```bash
git status              # cek perubahan & posisi branch
git branch              # daftar branch lokal
git branch -a           # daftar semua branch (lokal + remote)
git log --oneline -10   # riwayat commit
git pull origin <branch> # tarik kode terbaru (mis. main / refactor-v2)
```

### Migrasi database
```bash
php artisan migrate:status      # lihat status Ran / Pending
php artisan migrate --force      # jalankan migration di production (wajib --force)
# php artisan migrate:fresh      # ⚠️ JANGAN dijalankan di production (menghapus semua data)
# php artisan migrate:rollback --step=1 --force  # hati-hati, lihat bagian 5.2
```

### Cache / config / storage
```bash
php artisan optimize:clear   # bersihkan semua cache
php artisan optimize         # rebuild cache/config (untuk production)
php artisan storage:link     # buat symlink public/storage -> storage/app/public
php artisan key:generate     # hanya untuk instal baru, JANGAN untuk production yang sudah jalan
```

### Build asset (jika ada perubahan frontend)
```bash
npm install --ignore-scripts
npm run build
```

### Install dependency (produksi)
```bash
composer install --no-dev --optimize-autoloader
```

### Lokasi PHP production
Hostinger memakai PHP 8.4 dengan path berikut (dipakai di `deploy.yml`):
```bash
/opt/alt/php84/usr/bin/php artisan optimize:clear
```

---

## Catatan Keamanan
- Dokumentasi ini **tidak berisi** password, API key, SSH key, database credential, atau secret apa pun.
- Jangan pernah menulis/membagikan isi `.env` (terutama `APP_KEY`, `DB_PASSWORD`, `DB_USERNAME`).
- Semua operasi produksi dilakukan dengan backup yang tervalidasi dan, jika destruktif, dengan persetujuan.
