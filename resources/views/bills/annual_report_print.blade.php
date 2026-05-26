<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Tahunan Kas & Iuran Warga - Tahun {{ $year }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 30px;
            background-color: #ffffff;
        }
        
        /* Print Styles */
        @media print {
            body {
                padding: 0;
                margin: 0;
                font-size: 11px;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
        }

        /* Non-print control bar */
        .print-control-bar {
            background-color: #f1f5f9;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px border #cbd5e1;
        }
        .btn {
            background-color: #0f766e;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0d9488;
        }

        /* Formal Document Layout */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #0f766e;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #64748b;
            font-size: 12px;
            font-weight: bold;
        }

        /* Key Metrics Grid */
        .metrics-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-collapse: separate;
            border-spacing: 15px 0;
        }
        .metric-card {
            display: table-cell;
            width: 50%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }
        .metric-card h3 {
            margin: 0 0 10px 0;
            font-size: 11px;
            text-transform: uppercase;
            color: #0f766e;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .metric-row.total {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
            margin-top: 8px;
            color: #0f766e;
        }

        /* Section Heading */
        .section-title {
            font-size: 13px;
            text-transform: uppercase;
            color: #0f766e;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 4px;
            margin: 25px 0 12px 0;
            font-weight: bold;
        }

        /* Table design */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }

        /* Sign-off signatures */
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }
        .sig-col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
        }
        .sig-box {
            margin-top: 50px;
            border-top: 1px solid #1e293b;
            display: inline-block;
            width: 150px;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    {{-- Control Bar (Hidden on print) --}}
    <div class="print-control-bar no-print">
        <div>
            <strong>Laporan Siap Dicetak.</strong> Gunakan tombol di samping untuk mencetak laporan atau menyimpannya langsung menjadi file PDF resmi tingkat RW.
        </div>
        <div>
            <button onclick="window.print()" class="btn">Mulai Cetak / Simpan PDF</button>
        </div>
    </div>

    {{-- Header --}}
    <div class="header">
        <h1>Laporan Keuangan Tahunan Kas & Iuran Warga</h1>
        <p>Konsolidasi Keuangan Tingkat Kartu Keluarga (KK) & Rukun Tetangga (RT) · Tahun Laporan: {{ $year }}</p>
    </div>

    {{-- Core Stats Grid --}}
    <div class="metrics-grid">
        <!-- Kas Buku Besar -->
        <div class="metric-card">
            <h3>1. Ringkasan Buku Kas Umum (Buku Besar)</h3>
            <div class="metric-row">
                <span>Total Pemasukan:</span>
                <span class="font-bold">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
            <div class="metric-row">
                <span>Total Pengeluaran:</span>
                <span class="font-bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
            <div class="metric-row total">
                <span>Saldo Akhir Tahun:</span>
                <span>Rp {{ number_format($finalBalance, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Iuran Tagihan -->
        <div class="metric-card">
            <h3>2. Rekapitulasi Tagihan Iuran Bulanan</h3>
            <div class="metric-row">
                <span>Total Nilai Tagihan:</span>
                <span class="font-bold">Rp {{ number_format($totalBillsAmount, 0, ',', '.') }}</span>
            </div>
            <div class="metric-row">
                <span>Iuran Lunas Terbayar:</span>
                <span class="font-bold">Rp {{ number_format($totalBillPayments, 0, ',', '.') }}</span>
            </div>
            <div class="metric-row total">
                <span>Total Tunggakan Tertagih:</span>
                <span class="font-bold" style="color: #b91c1c;">Rp {{ number_format($totalArrearsAmount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Categories Section --}}
    <div class="section-title">A. Laporan Rincian Saldo Per Kategori Dana</div>
    <table>
        <thead>
            <tr>
                <th>Nama Kategori Dana</th>
                <th class="text-center" style="width: 100px;">Jenis</th>
                <th class="text-right">Pemasukan Kas ({{ $year }})</th>
                <th class="text-right">Pengeluaran Kas ({{ $year }})</th>
                <th class="text-right">Saldo Kas Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoriesSummary as $cat)
                <tr>
                    <td class="font-bold">{{ $cat->name }}</td>
                    <td class="text-center font-bold">
                        {{ $cat->is_mandatory ? 'Wajib' : 'Sukarela' }}
                    </td>
                    <td class="text-right font-bold" style="color: #0f766e;">Rp {{ number_format($cat->income ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($cat->expense ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($cat->final_balance, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- RT Section --}}
    <div class="section-title">B. Laporan Keberhasilan Pengumpulan Iuran Per RT</div>
    <table>
        <thead>
            <tr>
                <th>Nama Rukun Tetangga (RT)</th>
                <th class="text-center" style="width: 120px;">Jumlah Keluarga (KK)</th>
                <th class="text-right">Target Tagihan Wajib</th>
                <th class="text-right">Iuran Tertagih</th>
                <th class="text-right">Sisa Tunggakan RT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rtsSummary as $rt)
                <tr>
                    <td class="font-bold">RT {{ $rt->rt_number }}</td>
                    <td class="text-center font-bold">{{ $rt->kks_count }} KK</td>
                    <td class="text-right font-bold">Rp {{ number_format($rt->bills_amount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #0f766e;">Rp {{ number_format($rt->payments_amount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($rt->arrears_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- Arrears Section --}}
    <div class="header" style="margin-top: 20px;">
        <h1>Lampiran Laporan Keuangan</h1>
        <p>Daftar Rincian Tunggakan Tagihan Akhir Tahun {{ $year }}</p>
    </div>
    
    <div class="section-title" style="margin-top: 0;">C. Lampiran Daftar Warga Menunggak</div>
    <table>
        <thead>
            <tr>
                <th>Kode Tagihan</th>
                <th>Kepala Keluarga</th>
                <th class="text-center">RT</th>
                <th>Kategori Dana</th>
                <th class="text-center">Bulan Tagihan</th>
                <th class="text-right">Tagihan Asli</th>
                <th class="text-right">Sisa Tunggakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unpaidBills as $b)
                <tr>
                    <td class="font-bold font-mono">{{ $b->bill_code }}</td>
                    <td class="font-bold">Keluarga {{ $b->kk->family_head }}</td>
                    <td class="text-center">RT {{ $b->kk->rt->rt_number }}</td>
                    <td>{{ $b->fundCategory->name }}</td>
                    <td class="text-center">
                        {{ [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ][$b->month] ?? $b->month }}
                    </td>
                    <td class="text-right">Rp {{ number_format($b->amount, 0, ',', '.') }}</td>
                    <td class="text-right font-bold" style="color: #b91c1c;">Rp {{ number_format($b->outstanding_balance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center font-bold" style="color: #0f766e; padding: 25px;">
                        Luar Biasa! Seluruh tagihan warga lunas tertagih untuk tahun {{ $year }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-col">
            Dibuat oleh,<br>
            <strong>Bendahara RW</strong>
            <div class="sig-box">
                ( ................................. )
            </div>
        </div>
        <div class="sig-col">
            &nbsp;
        </div>
        <div class="sig-col">
            Mengetahui,<br>
            <strong>Ketua RW</strong>
            <div class="sig-box">
                ( ................................. )
            </div>
        </div>
    </div>

</body>
</html>
