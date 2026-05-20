# Use Case Diagram — Ecobank026 Custom

Diagram use case sistem informasi pengelolaan Kas RT/RW dan Bank Sampah.

## Diagram

```mermaid
flowchart LR
    %% Actors
    AdminRT([Admin RT])
    Bendahara([Bendahara])
    AdminBS([Admin Bank Sampah])
    Warga([Warga])

    %% === Shared Use Cases ===
    Login[/Login/]
    LihatDashboard[/Lihat Dashboard/]
    ExportLaporan[/Export Laporan/]

    %% === Community Cash Use Cases ===
    subgraph CC["Modul Kas RT/RW"]
        KelolaKategori[Kelola Kategori Dana]
        KelolaPemasukan[Kelola Pemasukan Warga]
        KelolaPengeluaran[Kelola Pengeluaran Dana]
        LihatBukuKas[Lihat Buku Kas]
    end

    %% === Bank Sampah Use Cases ===
    subgraph BS["Modul Bank Sampah"]
        KelolaDataNasabah[Kelola Data Warga/Nasabah]
        KelolaKategoriSampah[Kelola Kategori Sampah]
        KelolaHargaSampah[Kelola Harga Sampah]
        ImportHargaCSV[Import Harga CSV]
        KelolaSetoran[Kelola Setoran Sampah]
        KelolaPenarikan[Kelola Penarikan Saldo]
        KelolaPenjualan[Kelola Penjualan Sampah]
        LihatKasBS[Lihat Kas Bank Sampah]
    end

    %% === Warga Use Cases ===
    subgraph WG["Modul Warga"]
        LihatSaldo[Lihat Saldo Tabungan]
        LihatRiwayat[Lihat Riwayat Tabungan]
        LihatLaporanKas[Lihat Laporan Kas Warga]
    end

    %% === Admin RT Connections ===
    AdminRT --> Login
    AdminRT --> LihatDashboard
    AdminRT --> KelolaKategori
    AdminRT --> KelolaPemasukan
    AdminRT --> KelolaPengeluaran
    AdminRT --> LihatBukuKas
    AdminRT --> ExportLaporan
    AdminRT -.->|read-only| LihatKasBS

    %% === Bendahara Connections ===
    Bendahara --> Login
    Bendahara --> LihatDashboard
    Bendahara --> KelolaKategori
    Bendahara --> KelolaPemasukan
    Bendahara --> KelolaPengeluaran
    Bendahara --> LihatBukuKas
    Bendahara --> ExportLaporan

    %% === Admin Bank Sampah Connections ===
    AdminBS --> Login
    AdminBS --> LihatDashboard
    AdminBS --> KelolaDataNasabah
    AdminBS --> KelolaKategoriSampah
    AdminBS --> KelolaHargaSampah
    AdminBS --> ImportHargaCSV
    AdminBS --> KelolaSetoran
    AdminBS --> KelolaPenarikan
    AdminBS --> KelolaPenjualan
    AdminBS --> LihatKasBS
    AdminBS --> ExportLaporan

    %% === Warga Connections ===
    Warga --> Login
    Warga --> LihatDashboard
    Warga --> LihatSaldo
    Warga --> LihatRiwayat
    Warga --> LihatLaporanKas
```

## Keterangan Aktor

| Aktor | Deskripsi |
|-------|-----------|
| Admin RT | Full akses Kas RT/RW + read-only laporan Bank Sampah |
| Bendahara | Full akses Kas RT/RW, tidak bisa akses Bank Sampah |
| Admin Bank Sampah | Full akses Bank Sampah, tidak bisa akses Kas RT/RW |
| Warga | Read-only: laporan kas, saldo tabungan, riwayat transaksi |

## Catatan

- Garis putus-putus (`-.->`) menandakan akses read-only.
- Setiap aktor harus login terlebih dahulu sebelum mengakses fitur.
- Permission dikontrol menggunakan Spatie Laravel Permission.
