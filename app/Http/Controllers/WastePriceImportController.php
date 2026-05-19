<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use Illuminate\Http\Request;

class WastePriceImportController extends Controller
{
    public function showForm()
    {
        return view('bank-sampah.waste-prices.import');
    }

    public function template()
    {
        $filename = 'template-harga-sampah.csv';
        $headers = ['collector_name', 'waste_category_name', 'unit', 'member_price', 'collector_price'];
        $sample = [
            ['Bu Erta', 'Botol Putih Bersih', 'kg', '2800', '3100'],
            ['Bu Erta', 'Botol Putih Kotor', 'kg', '1500', '1800'],
            ['Bu Erta', 'Kardus Bersih', 'kg', '1200', '1500'],
        ];

        return response()->streamDownload(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($sample as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->withErrors(['file' => 'Gagal membaca file.']);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['file' => 'File kosong.']);
        }

        // Normalize header
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);
        $required = ['collector_name', 'waste_category_name', 'member_price', 'collector_price'];
        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            fclose($handle);
            return back()->withErrors(['file' => 'Kolom wajib tidak ditemukan: ' . implode(', ', $missing)]);
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            $mapped = array_combine($header, array_pad($data, count($header), ''));

            $collectorName = trim($mapped['collector_name'] ?? '');
            $categoryName = trim($mapped['waste_category_name'] ?? '');
            $unit = trim($mapped['unit'] ?? '') ?: 'kg';
            $memberPrice = $mapped['member_price'] ?? '';
            $collectorPrice = $mapped['collector_price'] ?? '';

            // Validate row
            if ($collectorName === '' || $categoryName === '') {
                $errors[] = "Baris {$row}: collector_name dan waste_category_name wajib diisi.";
                continue;
            }
            if (!is_numeric($memberPrice) || $memberPrice < 0) {
                $errors[] = "Baris {$row}: member_price harus angka >= 0.";
                continue;
            }
            if (!is_numeric($collectorPrice) || $collectorPrice < 0) {
                $errors[] = "Baris {$row}: collector_price harus angka >= 0.";
                continue;
            }
            if ((float) $collectorPrice < (float) $memberPrice) {
                $errors[] = "Baris {$row}: collector_price harus >= member_price.";
                continue;
            }

            $collector = Collector::firstOrCreate(
                ['name' => $collectorName],
                ['phone' => null, 'address' => null]
            );

            $category = WasteCategory::firstOrCreate(
                ['name' => $categoryName],
                ['unit' => $unit]
            );

            $existing = WastePrice::where('collector_id', $collector->id)
                ->where('waste_category_id', $category->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'member_price' => (float) $memberPrice,
                    'collector_price' => (float) $collectorPrice,
                    'price_per_unit' => (float) $memberPrice,
                ]);
                $updated++;
            } else {
                WastePrice::create([
                    'collector_id' => $collector->id,
                    'waste_category_id' => $category->id,
                    'member_price' => (float) $memberPrice,
                    'collector_price' => (float) $collectorPrice,
                    'price_per_unit' => (float) $memberPrice,
                ]);
                $created++;
            }
        }

        fclose($handle);

        return back()->with('import_result', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
