# Activity Diagram — Modul Kas RT/RW (Community Cash)

Diagram aktivitas alur pengelolaan kas RT/RW meliputi pemasukan dan pengeluaran dana.

## A. Alur Pemasukan (Kontribusi Warga)

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login sebagai Admin RT / Bendahara]
    Login --> Dashboard[Buka Dashboard]
    Dashboard --> PilihKategori[Pilih Kategori Dana]
    PilihKategori --> InputPemasukan[Input Data Pemasukan]
    InputPemasukan --> IsiForm[Isi: Nama Warga, Jumlah, Tanggal, Keterangan]
    IsiForm --> Simpan[Simpan Pemasukan]
    Simpan --> UpdateLedger[Sistem Buat Entry Ledger - Tipe: credit]
    UpdateLedger --> UpdateBalance[Sistem Update Balance Kategori]
    UpdateBalance --> Selesai([Selesai])
```

## B. Alur Pengeluaran Dana

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login sebagai Admin RT / Bendahara]
    Login --> PilihKategori[Pilih Kategori Dana]
    PilihKategori --> InputPengeluaran[Input Data Pengeluaran]
    InputPengeluaran --> IsiForm[Isi: Jumlah, Tanggal, Keterangan]
    IsiForm --> ValidasiSaldo{Saldo kategori cukup?}
    ValidasiSaldo -->|Ya| Simpan[Simpan Pengeluaran]
    ValidasiSaldo -->|Tidak| Tolak[Tampilkan Error: Saldo Tidak Cukup]
    Tolak --> InputPengeluaran
    Simpan --> UpdateLedger[Sistem Buat Entry Ledger - Tipe: debit]
    UpdateLedger --> UpdateBalance[Sistem Update Balance Kategori]
    UpdateBalance --> Selesai([Selesai])
```

## C. Alur Lengkap Kas RT/RW

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Login Admin RT / Bendahara]
    Login --> Dashboard[Lihat Dashboard Kas RT/RW]
    Dashboard --> Pilih{Pilih Aksi}

    Pilih -->|Pemasukan| PilihKat1[Pilih Kategori Dana]
    PilihKat1 --> InputMasuk[Input Pemasukan Warga]
    InputMasuk --> SimpanMasuk[Simpan]
    SimpanMasuk --> LedgerCredit[Update Ledger: credit]
    LedgerCredit --> BalanceUp[Balance Kategori Bertambah]

    Pilih -->|Pengeluaran| PilihKat2[Pilih Kategori Dana]
    PilihKat2 --> InputKeluar[Input Pengeluaran]
    InputKeluar --> CekSaldo{Saldo Cukup?}
    CekSaldo -->|Ya| SimpanKeluar[Simpan]
    CekSaldo -->|Tidak| Error[Error: Saldo Tidak Cukup]
    Error --> InputKeluar
    SimpanKeluar --> LedgerDebit[Update Ledger: debit]
    LedgerDebit --> BalanceDown[Balance Kategori Berkurang]

    Pilih -->|Laporan| BukuKas[Lihat Buku Kas Umum]
    BukuKas --> Filter[Filter Tanggal & Kategori]
    Filter --> Export{Export?}
    Export -->|Ya| CSV[Download CSV]
    Export -->|Tidak| Selesai

    BalanceUp --> Selesai([Selesai])
    BalanceDown --> Selesai
    CSV --> Selesai
```

## Keterangan

- **Ledger** adalah sumber kebenaran (source of truth) untuk saldo setiap kategori dana.
- Setiap pemasukan menghasilkan entry ledger bertipe `credit`.
- Setiap pengeluaran menghasilkan entry ledger bertipe `debit`.
- Pengeluaran tidak boleh melebihi saldo kategori dana yang bersangkutan.
- Edit/delete transaksi otomatis melakukan recalculate pada ledger.
