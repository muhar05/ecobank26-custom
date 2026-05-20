# Activity Diagram — Modul Bank Sampah

Diagram aktivitas alur operasional bank sampah: setoran, penarikan, dan penjualan.

## A. Alur Setoran Sampah (Deposit)

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login sebagai Admin Bank Sampah]
    Login --> PilihNasabah[Pilih Nasabah]
    PilihNasabah --> PilihPengepul[Pilih Pengepul]
    PilihPengepul --> PilihKategori[Pilih Kategori Sampah]
    PilihKategori --> AmbilHarga[Sistem Ambil member_price dari WastePrice]
    AmbilHarga --> InputBerat[Input Berat Sampah]
    InputBerat --> HitungSubtotal[Sistem Hitung Subtotal = Berat × member_price]
    HitungSubtotal --> TambahItem{Tambah Item Lain?}
    TambahItem -->|Ya| PilihKategori
    TambahItem -->|Tidak| HitungTotal[Sistem Hitung Total Amount]
    HitungTotal --> SimpanSetoran[Simpan Setoran + Detail]
    SimpanSetoran --> UpdateSavings[Update Savings Ledger: credit]
    UpdateSavings --> UpdateSaldo[Saldo Nasabah Bertambah]
    UpdateSaldo --> Selesai([Selesai])
```

## B. Alur Penarikan Saldo (Withdrawal)

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login sebagai Admin Bank Sampah]
    Login --> PilihNasabah[Pilih Nasabah]
    PilihNasabah --> InputJumlah[Input Jumlah Penarikan]
    InputJumlah --> CekSetoran{Nasabah sudah 2x setoran?}
    CekSetoran -->|Tidak| TolakSetoran[Error: Minimal 2x Setoran]
    TolakSetoran --> PilihNasabah
    CekSetoran -->|Ya| CekSaldo{Saldo Cukup?}
    CekSaldo -->|Tidak| TolakSaldo[Error: Saldo Tidak Cukup]
    TolakSaldo --> InputJumlah
    CekSaldo -->|Ya| SimpanPenarikan[Simpan Penarikan]
    SimpanPenarikan --> UpdateSavings[Update Savings Ledger: debit]
    UpdateSavings --> UpdateSaldo[Saldo Nasabah Berkurang]
    UpdateSaldo --> Selesai([Selesai])
```

## C. Alur Penjualan ke Pengepul (Sale)

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login sebagai Admin Bank Sampah]
    Login --> PilihPengepul[Pilih Pengepul]
    PilihPengepul --> PilihKategori[Pilih Kategori Sampah]
    PilihKategori --> AmbilHarga[Sistem Ambil collector_price dari WastePrice]
    AmbilHarga --> InputBerat[Input Berat Sampah]
    InputBerat --> HitungSubtotal[Sistem Hitung Subtotal = Berat × collector_price]
    HitungSubtotal --> TambahItem{Tambah Item Lain?}
    TambahItem -->|Ya| PilihKategori
    TambahItem -->|Tidak| HitungTotal[Sistem Hitung Total Penjualan]
    HitungTotal --> HitungMargin[Sistem Hitung Margin = collector_price − member_price]
    HitungMargin --> SimpanPenjualan[Simpan Penjualan + Detail]
    SimpanPenjualan --> UpdateKasBS[Update Waste Bank Cash Ledger: credit]
    UpdateKasBS --> UpdateSaldoKas[Kas Bank Sampah Bertambah Sebesar Margin]
    UpdateSaldoKas --> Selesai([Selesai])
```

## D. Alur Lengkap Bank Sampah

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login Admin Bank Sampah]
    Login --> Dashboard[Lihat Dashboard Bank Sampah]
    Dashboard --> Pilih{Pilih Aksi}

    Pilih -->|Setoran| Deposit[Catat Setoran Sampah]
    Deposit --> SavingsCredit[Savings Ledger: credit]
    SavingsCredit --> SaldoNaik[Saldo Nasabah +]

    Pilih -->|Penarikan| Withdrawal[Catat Penarikan]
    Withdrawal --> ValidasiW{Valid?}
    ValidasiW -->|Ya| SavingsDebit[Savings Ledger: debit]
    ValidasiW -->|Tidak| ErrorW[Error Validasi]
    SavingsDebit --> SaldoTurun[Saldo Nasabah −]

    Pilih -->|Penjualan| Sale[Catat Penjualan ke Pengepul]
    Sale --> MarginCalc[Hitung Margin]
    MarginCalc --> KasCredit[Waste Bank Cash Ledger: credit]
    KasCredit --> KasNaik[Kas Bank Sampah +]

    Pilih -->|Laporan| Laporan[Lihat Laporan]
    Laporan --> Export[Export CSV]

    SaldoNaik --> Selesai([Selesai])
    SaldoTurun --> Selesai
    ErrorW --> Pilih
    KasNaik --> Selesai
    Export --> Selesai
```

## Keterangan

- **member_price**: Harga yang diterima nasabah saat menyetor sampah.
- **collector_price**: Harga jual ke pengepul.
- **Margin**: Selisih `collector_price − member_price`, masuk ke kas bank sampah.
- **Savings Ledger**: Mencatat mutasi saldo tabungan nasabah (credit/debit).
- **Waste Bank Cash Ledger**: Mencatat kas operasional bank sampah (hanya dari margin).
- Penarikan memerlukan minimal 2 kali setoran sebelumnya.
- Penarikan tidak boleh melebihi saldo nasabah.
