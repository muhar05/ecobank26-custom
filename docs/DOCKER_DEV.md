# Docker Development — Ecobank026

Dokumen ini menjelaskan cara menjalankan project **Bank Sampah / Kas RT-RW (Ecobank026)**
menggunakan **Docker Compose** sebagai environment development dan opsi portability.

> Docker bersifat **opsional**. Native development yang sudah ada dan deployment
> production di Hostinger **tidak terpengaruh** oleh setup ini.

---

## 1. Prasyarat Docker

- **Docker Engine** + **Docker Compose** terpasang.
  - Docker Desktop (Mac/Windows) sudah menyertakan Compose.
  - Linux: `docker` + plugin `docker compose` (v2).
- Port bebas di host: **8000** (Laravel), **5173** (Vite), **3307** (MySQL Docker).
  - MySQL Docker memakai port host `3307` agar tidak bentrok dengan MySQL native di `3306`.
- Cek instalasi:

```bash
docker --version
docker compose version
```

---

## 2. Cara Pertama Kali Menjalankan Project

Langkah ini membuat file `.env` dari template khusus Docker. **Tidak mengubah**
`.env` native Anda selama Anda menyalin ke `.env` baru.

```bash
cp .env.docker.example .env
```

Buat image & mulai seluruh container:

```bash
docker compose up -d --build
```

> `--build` wajib pada kali pertama agar image PHP dibangun.

Masukkan dependency & key (sekali saja):

```bash
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan storage:link
```

Assets siap pakai (opsional, hanya jika Vite tidak dipakai):

```bash
docker compose run --rm node npm install --ignore-scripts
docker compose run --rm node npm run build
```

Buka aplikasi: http://localhost:8000

---

## 3. Menjalankan Laravel

Container `app` otomatis menjalankan `php artisan serve --host=0.0.0.0 --port=8000`
setelah `docker compose up`.

Akses di browser: **http://localhost:8000**

Untuk perintah Artisan dari dalam container:

```bash
docker compose exec app php artisan about
docker compose exec app php artisan optimize:clear
```

---

## 4. Menjalankan Vite

Container `node` otomatis menjalankan Vite dev server (HMR) pada port `5173`
dan melakukan `npm install` di kali pertama.

Pastikan container node aktif:

```bash
docker compose logs -f node
```

Dengan HMR aktif, Laravel otomatis memuat asset dari Vite (file `public/hot`).
Jika tidak ingin memakai HMR, jalankan build statis:

```bash
docker compose run --rm node npm run build
```

---

## 5. Menjalankan Migration / Seeder

```bash
# Jalankan semua migration
docker compose exec app php artisan migrate

# Seed awal (admin, role, dsb.)
docker compose exec app php artisan db:seed

# Seed dengan data demo
docker compose exec app php artisan db:seed --class=DemoSeeder

# Refresh database + seeder (HANYA di dev, JANGAN di production)
docker compose exec app php artisan migrate:fresh --seed
```

---

## 6. Masuk ke Container

Masuk ke shell interaktif container `app`:

```bash
docker compose exec app sh
```

Masuk sebagai root user (untuk mengubah permission file):

```bash
docker compose exec -u root app sh
```

Masuk ke container database:

```bash
docker compose exec db mysql -uecobank -p
```

---

## 7. Menghentikan Container

```bash
# Hentikan semua container
docker compose stop

# Hentikan & hapus container (data volume tetap tersimpan)
docker compose down
```

---

## 8. Reset Environment Docker

> Data database tetap aman karena tersimpan di named volume. Reset ini hanya
> menghapus container & memulai ulang, **tidak menghapus data** kecuali dihapus volumenya.

```bash
# Hentikan & hapus container + network (volume tetap ada)
docker compose down

# Mulai ulang bersih
docker compose up -d --build
```

Mengosongkan **seluruh** data database Docker (hapus volume):

```bash
docker compose down -v
docker compose up -d --build
docker compose run --rm app php artisan migrate --seed
```

> `-v` menghapus semua volume (database + vendor + node_modules + storage).
> Vendor & node_modules akan terpasang ulang saat container `node` start / saat
> `composer install` dijalankan.

Perintah ringkas harian:

| Tujuan | Command |
|---|---|
| Build container | `docker compose up -d --build` |
| Start | `docker compose up -d` |
| Stop | `docker compose stop` |
| Restart | `docker compose restart` |
| Masuk container | `docker compose exec app sh` |
| Artisan | `docker compose exec app php artisan ...` |
| Composer | `docker compose run --rm app composer ...` |
| NPM/Vite | `docker compose exec node npm ...` |
| Migration | `docker compose exec app php artisan migrate` |
| Logs | `docker compose logs -f app` |

---

## 9. Perbedaan Docker Development vs Production Hostinger

| Aspek | Docker (dev) | Hostinger (production) |
|---|---|---|
| PHP | 8.4 (container, `php:8.4-cli-alpine`) | 8.4 (`/opt/alt/php84/usr/bin/php`) |
| Web server | `php artisan serve` (built-in) | Web server (Apache/nginx hPanel) |
| Database | MySQL 8.0 container (port host `3307`) | MySQL managed Hostinger |
| Asset build | Vite HMR (`:5173`) atau `npm run build` | `npm run build` → `public/build` |
| Migration | manual `php artisan migrate` | `php artisan migrate --force` |
| `APP_DEBUG` | `true` | `false` |
| `.env` | `.env` hasil `cp .env.docker.example` | `.env` production (tidak di-repo) |
| Data | named volume `ecobank026-db-data` | database managed host |

### Cara berpindah dari Docker ke Hostinger (untuk saat dibutuhkan)

1. Ekspor database Docker: `mysqldump` dari container → import via phpMyAdmin hPanel.
2. Tarik kode di server, `composer install --no-dev`.
3. Isi `.env` production (jangan ikutkan nilai Docker).
4. `npm install --ignore-scripts && npm run build`.
5. `php artisan migrate --force`, `php artisan optimize:clear`, `php artisan optimize`.
6. `php artisan storage:link`.

Lihat `docs/DEPLOYMENT_PRODUCTION.md` untuk panduan production lengkap.

---

## 10. Catatan & File Konfigurasi

| File | Fungsi |
|---|---|
| `compose.yaml` | Orkestrasi service `app`, `db`, `node`; volume; network; port |
| `Dockerfile` | Image PHP 8.4 + extension yang dibutuhkan + Composer |
| `docker/php/php.ini` | Konfigurasi PHP (memory, upload, opcache) |
| `.env.docker.example` | Template `.env` untuk Docker dev (tanpa secret production) |
| `.dockerignore` | Mengecualikan file non-runtime dari build context |
| `vite.config.js` | `server.host/hmr` agar Vite bisa diakses dari browser via container |

### Extension PHP yang dipasang di Docker

`bcmath`, `exif`, `fileinfo`, `gd`, `intl`, `mbstring`, `opcache`, `pcntl`,
`pdo_mysql`, `zip` — sesuai kebutuhan project (Excel/import, spatie/permission, dll).

### Credential

Tidak ada credential production yang di-hardcode. Nilai default di compose hanya
untuk development lokal (`ecobank` / `ecobank_dev`). Ubah lewat `.env` sebelum dipakai.
