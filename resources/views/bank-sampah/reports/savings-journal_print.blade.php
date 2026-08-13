<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Jurnal Tabungan</title>
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
        <h1>Jurnal Tabungan Nasabah Bank Sampah</h1>
        <p>Periode: {{ $startDate->toDateString() }} s/d {{ $endDate->toDateString() }}</p>
        <p>Dicetak oleh: {{ auth()->user()->name }} pada {{ now()->toDateTimeString() }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="label">Total Setoran</div>
            <div class="value" style="color: #16a34a;">Rp {{ number_format($totalSetor, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Penarikan</div>
            <div class="value" style="color: #dc2626;">Rp {{ number_format($totalTarik, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Net Mutasi</div>
            <div class="value">Rp {{ number_format($totalSetor - $totalTarik, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Nasabah</th>
                <th style="width: 15%;">Tipe</th>
                <th style="width: 25%;">Deskripsi</th>
                <th style="width: 10%;" class="text-right">Nominal</th>
                <th style="width: 10%;" class="text-right">Running Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledgers as $l)
                <tr>
                    <td>{{ $l->created_at ? $l->created_at->toDateString() : '-' }}</td>
                    <td style="font-weight: bold;">
                        @if($l->wasteCustomer)
                            {{ $l->wasteCustomer->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $l->type === 'credit' ? 'Setoran (Credit)' : 'Penarikan (Debit)' }}</td>
                    <td>{{ $l->description ?? '-' }}</td>
                    <td class="text-right" style="font-weight: bold; color: {{ $l->type === 'credit' ? '#16a34a' : '#dc2626' }};">
                        {{ $l->type === 'credit' ? '+' : '-' }} Rp {{ number_format($l->amount, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        @if(isset($l->running_balance))
                            Rp {{ number_format($l->running_balance, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data mutasi tabungan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
