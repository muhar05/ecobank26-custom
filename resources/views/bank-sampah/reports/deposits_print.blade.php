<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Setoran Sampah</title>
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
        <h1>Laporan Setoran Sampah Bank Sampah</h1>
        <p>Periode: {{ $startDate->toDateString() }} s/d {{ $endDate->toDateString() }}</p>
        <p>Dicetak oleh: {{ auth()->user()->name }} pada {{ now()->toDateTimeString() }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ number_format($totalTransactions, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Berat</div>
            <div class="value">{{ number_format($totalWeight, 2, ',', '.') }} kg</div>
        </div>
        <div class="summary-item">
            <div class="label">Total Nilai Setoran</div>
            <div class="value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="label">Rata-rata Transaksi</div>
            <div class="value">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Kode</th>
                <th style="width: 25%;">Nasabah</th>
                <th style="width: 20%;">Kategori</th>
                <th style="width: 10%;" class="text-right">Berat</th>
                <th style="width: 10%;" class="text-right">Harga</th>
                <th style="width: 10%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $weightTotal = 0; $amountTotal = 0; @endphp
            @forelse($details as $d)
                @php
                    $weightTotal += (float) $d->weight;
                    $amountTotal += (float) $d->subtotal;
                @endphp
                <tr>
                    <td>{{ $d->deposit->date ? $d->deposit->date->toDateString() : '-' }}</td>
                    <td style="font-family: monospace;">DEP-{{ str_pad($d->deposit_id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-weight: bold;">
                        @if($d->deposit->wasteCustomer)
                            {{ $d->deposit->wasteCustomer->name }}
                        @elseif($d->deposit->member)
                            {{ $d->deposit->member->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $d->wasteCategory->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($d->weight, 2, ',', '.') }} kg</td>
                    <td class="text-right">Rp {{ number_format($d->price_per_unit, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data setoran.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">TOTAL</td>
                <td class="text-right">{{ number_format($weightTotal, 2, ',', '.') }} kg</td>
                <td></td>
                <td class="text-right">Rp {{ number_format($amountTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
