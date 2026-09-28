<?php

namespace App\Http\Controllers;

use App\Models\Collector;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WastePriceTemplateExport;
use App\Exports\WastePriceFailedRowsExport;
use Carbon\Carbon;

class WastePriceImportController extends Controller
{
    public function showForm()
    {
        $collectors = Collector::orderBy('name')->get();
        return view('bank-sampah.waste-prices.import', compact('collectors'));
    }

    public function template()
    {
        return Excel::download(new WastePriceTemplateExport(), 'template-harga-sampah.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'collector_id' => 'required|exists:collectors,id',
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        $collector = Collector::findOrFail($request->collector_id);
        $file = $request->file('file');
        
        try {
            $sheets = Excel::toArray(new WastePriceTemplateExport(), $file);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca file Excel. Pastikan format file benar.']);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return back()->withErrors(['file' => 'File kosong atau sheet DATA IMPORT tidak ditemukan.']);
        }

        $importData = $sheets[0];
        
        // Remove header row (index 0)
        $header = array_shift($importData);

        $created = 0;
        $updated = 0;
        $failedRows = [];
        $errors = [];
        $rowNumber = 1;

        foreach ($importData as $row) {
            $rowNumber++;

            // Skip completely empty rows
            $nonEmptyCells = array_filter($row, fn($cell) => $cell !== null && trim($cell) !== '');
            if (empty($nonEmptyCells)) {
                continue;
            }

            $groupCode = isset($row[0]) ? trim($row[0]) : '';
            $groupName = isset($row[1]) ? trim($row[1]) : '';
            $categoryCode = isset($row[2]) ? trim($row[2]) : '';
            $categoryName = isset($row[3]) ? trim($row[3]) : '';
            $unit = isset($row[4]) ? trim($row[4]) : '';
            $memberPrice = isset($row[5]) ? trim($row[5]) : '';
            $collectorPrice = isset($row[6]) ? trim($row[6]) : '';
            $tanggalBerlakuRaw = isset($row[7]) ? trim($row[7]) : '';

            $reason = null;

            // Wajib validation
            if ($categoryName === '') {
                $reason = 'Nama Kategori Sampah wajib diisi.';
            } elseif ($unit === '') {
                $reason = 'Satuan wajib diisi.';
            } elseif ($collectorPrice === '' || !is_numeric($collectorPrice) || $collectorPrice < 0) {
                $reason = 'Harga Jual ke Agregator wajib diisi dengan angka >= 0.';
            }

            if ($reason) {
                $rowWithReason = $row;
                $rowWithReason[8] = $reason;
                $failedRows[] = $rowWithReason;
                $errors[] = "Baris {$rowNumber}: {$reason}";
                continue;
            }

            // Category Lookup
            $category = null;
            if (!empty($categoryCode)) {
                $category = WasteCategory::where('code', $categoryCode)->first();
            }

            if (!$category && !empty($categoryName)) {
                // Look up by name and group
                $category = WasteCategory::where('name', $categoryName)
                    ->when(!empty($groupCode) || !empty($groupName), function($q) use ($groupCode, $groupName) {
                        $q->whereHas('wasteCategoryGroup', function($q2) use ($groupCode, $groupName) {
                            if (!empty($groupCode) && !empty($groupName)) {
                                $q2->where('code', $groupCode)->orWhere('name', $groupName);
                            } elseif (!empty($groupCode)) {
                                $q2->where('code', $groupCode);
                            } elseif (!empty($groupName)) {
                                $q2->where('name', $groupName);
                            }
                        });
                    })->first();

                // If not found and no group filter specified, check by name only
                if (!$category && empty($groupCode) && empty($groupName)) {
                    $category = WasteCategory::where('name', $categoryName)->first();
                }
            }

            if (!$category) {
                $rowWithReason = $row;
                $rowWithReason[8] = 'Kategori sampah belum terdaftar';
                $failedRows[] = $rowWithReason;
                $errors[] = "Baris {$rowNumber}: Kategori sampah belum terdaftar";
                continue;
            }

            // Validasi harga agregator terhadap harga beli nasabah master kategori
            if ((float) $collectorPrice < (float) $category->member_price) {
                $rowWithReason = $row;
                $rowWithReason[8] = "Harga Agregator (Rp " . number_format((float)$collectorPrice, 0, ',', '.') . ") tidak boleh lebih rendah dari Harga Beli Nasabah (Rp " . number_format((float)$category->member_price, 0, ',', '.') . ").";
                $failedRows[] = $rowWithReason;
                $errors[] = "Baris {$rowNumber}: {$rowWithReason[8]}";
                continue;
            }

            // Parse Date
            $tanggalBerlaku = now()->format('Y-m-d');
            if (!empty($tanggalBerlakuRaw)) {
                if (is_numeric($tanggalBerlakuRaw)) {
                    try {
                        $tanggalBerlaku = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalBerlakuRaw))->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tanggalBerlaku = now()->format('Y-m-d');
                    }
                } else {
                    try {
                        $parsedDate = Carbon::parse($tanggalBerlakuRaw);
                        if ($parsedDate->greaterThan(now()->addYear())) {
                            $rowWithReason = $row;
                            $rowWithReason[8] = 'Tanggal berlaku terlalu jauh di masa depan (maksimal 1 tahun)';
                            $failedRows[] = $rowWithReason;
                            $errors[] = "Baris {$rowNumber}: Tanggal berlaku terlalu jauh.";
                            continue;
                        }
                        $tanggalBerlaku = $parsedDate->format('Y-m-d');
                    } catch (\Exception $e) {
                        $rowWithReason = $row;
                        $rowWithReason[8] = 'Format tanggal tidak valid. Gunakan YYYY-MM-DD';
                        $failedRows[] = $rowWithReason;
                        $errors[] = "Baris {$rowNumber}: Tanggal tidak valid.";
                        continue;
                    }
                }
            }

            // Save/Update
            $existing = WastePrice::where('collector_id', $collector->id)
                ->where('waste_category_id', $category->id)
                ->first();

            if ($existing) {
                $existing->update([
                    'collector_price' => (float) $collectorPrice,
                ]);
                $updated++;
            } else {
                WastePrice::create([
                    'collector_id' => $collector->id,
                    'waste_category_id' => $category->id,
                    'collector_price' => (float) $collectorPrice,
                ]);
                $created++;
            }
        }

        // Store failed rows in session
        if (!empty($failedRows)) {
            session(['waste_price_import_failed_rows' => $failedRows]);
        } else {
            session()->forget('waste_price_import_failed_rows');
        }

        return back()->with('import_result', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
            'has_failed' => !empty($failedRows),
        ]);
    }

    public function downloadFailedRows()
    {
        $failedRows = session('waste_price_import_failed_rows');
        if (empty($failedRows)) {
            return back()->withErrors(['file' => 'Tidak ada baris gagal yang dapat diunduh.']);
        }

        return Excel::download(new WastePriceFailedRowsExport($failedRows), 'failed-rows-harga-sampah.xlsx');
    }
}
