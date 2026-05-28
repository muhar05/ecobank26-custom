<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Arus Kas</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 6px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .label {
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
            margin-bottom: 3px;
        }
        .summary-item .value {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8fafc;
        }
        
        @media print {
            body {
                margin: 10px;
            }
            .no-print {
                display: none;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>Laporan Arus Kas Bank Sampah</h1>
        <p>Periode: {{ $startDate->toDateString() }} s/d {{ $endDate->toDateString() }}</p>
        <p>Dicetak oleh: {{ auth()->user()->name }} pada {{ now()->toDateTimeString() }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="label">Total Pemasukan</div>
            <div class="value" style="color: #10b981;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Pengeluaran</div>
            <div class="value" style="color: #ef4444;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Net Flow Keuangan</div>
            <div class="value">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">Kode</th>
                <th style="width: 15%;">Tipe</th>
                <th style="width: 35%;">Deskripsi</th>
                <th style="width: 20%;" class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashbook as $item)
                <tr>
                    <td>{{ $item['date'] }}</td>
                    <td style="font-family: monospace;">{{ $item['code'] }}</td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-right" style="font-weight: bold; color: {{ $item['is_in'] ? '#047857' : '#b91c1c' }}">
                        {{ $item['is_in'] ? '+' : '-' }} Rp {{ number_format($item['amount'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data kas operasional.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">NET FLOW KEUANGAN PERIODE</td>
                <td class="text-right" style="color: {{ $saldoAkhir >= 0 ? '#047857' : '#b91c1c' }}">
                    Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
