<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kas</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: sans-serif; font-size: 11px; line-height: 1.3; color: #333; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 60px; height: 60px; margin-right: 15px; }
        .title-section { flex: 1; }
        h1 { font-size: 16px; margin: 0; color: #065f46; }
        .print-note { padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f0fdf4; color: #065f46; }
        .summary-box { background-color: #f8fafc; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body onload="window.print()">
    <div class="print-note">
        <strong>Tips Cetak:</strong> Untuk hasil terbaik, pastikan opsi <strong>"Headers and footers"</strong> pada dialog cetak <strong>TIDAK DICENTANG</strong>.
    </div>

    <div class="header">
        @php $logo = public_path('images/logo.png'); @endphp
        @if(file_exists($logo))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" class="logo">
        @endif
        <div class="title-section">
            <h1>Laporan Kas Warga</h1>
            <p style="margin: 2px 0;">RT/RW 026</p>
        </div>
    </div>

    <div class="summary-box">
        <p style="margin: 3px 0;">Periode: <strong>{{ request()->input('period_type', 'Bulanan') }}</strong></p>
        <p style="margin: 3px 0;">Total Pemasukan: Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
        <p style="margin: 3px 0;">Total Pengeluaran: Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
        <p style="margin: 3px 0; font-weight: bold;">Saldo Akhir: Rp {{ number_format($currentBalance, 0, ',', '.') }}</p>
        <p style="margin: 3px 0; font-size: 10px; color: #666;">Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th style="text-align: right;">Masuk</th>
                <th style="text-align: right;">Keluar</th>
                <th style="text-align: right;">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledgers as $l)
                <tr>
                    <td>{{ $l->date->format('Y-m-d') }}</td>
                    <td>{{ $l->description }}</td>
                    <td>{{ $l->fundCategory->name }}</td>
                    <td style="text-align: right;">{{ $l->type === 'in' ? number_format($l->amount, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right;">{{ $l->type === 'out' ? number_format($l->amount, 0, ',', '.') : '-' }}</td>
                    <td style="text-align: right;">{{ number_format($l->balance, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
