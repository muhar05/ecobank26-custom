<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use App\Models\WasteCategoryGroup;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WasteCategoryTemplateExport;
use App\Exports\WasteCategoryFailedRowsExport;
use Illuminate\Support\Facades\DB;

class WasteCategoryImportController extends Controller
{
    public function showForm()
    {
        return view('bank-sampah.waste-categories.import');
    }

    public function template()
    {
        return Excel::download(new WasteCategoryTemplateExport(), 'template-kategori-sampah.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'mode' => 'required|string|in:insert_only,insert_or_update,skip_duplicate',
        ]);

        $file = $request->file('file');
        $mode = $request->input('mode');

        try {
            $sheets = Excel::toArray(new WasteCategoryTemplateExport(), $file);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca file Excel. Pastikan format file benar.']);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return back()->withErrors(['file' => 'File kosong atau sheet DATA IMPORT tidak ditemukan.']);
        }

        $importData = $sheets[0];
        
        // Remove header row (index 0)
        $header = array_shift($importData);

        // Validation max row 1000
        if (count($importData) > 1000) {
            return back()->withErrors(['file' => 'Maksimal data yang diimport adalah 1000 baris.']);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $duplicate = 0;
        $failed = 0;
        $totalRows = 0;

        $failedRows = [];
        $errors = [];
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            foreach ($importData as $row) {
                $rowNumber++;

                // Skip completely empty rows
                $nonEmptyCells = array_filter($row, fn($cell) => $cell !== null && trim($cell) !== '');
                if (empty($nonEmptyCells)) {
                    continue;
                }

                $totalRows++;

                $groupCode = isset($row[0]) ? strtoupper(trim($row[0])) : '';
                $groupName = isset($row[1]) ? trim($row[1]) : '';
                $categoryCode = isset($row[2]) ? strtoupper(trim($row[2])) : '';
                $categoryName = isset($row[3]) ? trim($row[3]) : '';
                $unit = isset($row[4]) ? trim($row[4]) : '';
                $rawMemberPrice = isset($row[5]) ? trim((string) $row[5]) : '';

                // Harga Beli (member_price): opsional. String kosong = tidak diubah
                // (atau default 0 untuk kategori baru). Nilai 0 yang valid tetap dipakai.
                $memberPrice = null;
                if ($rawMemberPrice !== '' && (!is_numeric($rawMemberPrice) || (float) $rawMemberPrice < 0)) {
                    $memberPrice = false; // penanda harga tidak valid
                } elseif ($rawMemberPrice !== '') {
                    $memberPrice = (float) $rawMemberPrice;
                }

                $reason = null;

                // Wajib validation
                if ($groupCode === '' && $groupName === '') {
                    $reason = 'Kode Grup atau Nama Grup wajib diisi.';
                } elseif ($categoryName === '') {
                    $reason = 'Nama Kategori Sampah wajib diisi.';
                } elseif ($unit === '') {
                    $reason = 'Satuan wajib diisi.';
                } elseif ($memberPrice === false) {
                    $reason = 'Harga Beli harus berupa angka 0 atau lebih.';
                }

                if ($reason) {
                    $failed++;
                    $rowWithReason = $row;
                    $rowWithReason[6] = $reason;
                    $failedRows[] = $rowWithReason;
                    $errors[] = "Baris {$rowNumber}: {$reason}";
                    continue;
                }

                // Resolve Group
                $group = null;
                if (!empty($groupCode)) {
                    $group = WasteCategoryGroup::where('code', $groupCode)->first();
                }

                if (!$group && !empty($groupName)) {
                    $group = WasteCategoryGroup::where('name', 'like', $groupName)->first();
                }

                if (!$group) {
                    $failed++;
                    $rowWithReason = $row;
                    $rowWithReason[6] = 'Grup kategori belum terdaftar. Tambahkan dulu di menu Kelola Grup.';
                    $failedRows[] = $rowWithReason;
                    $errors[] = "Baris {$rowNumber}: Grup kategori '{$groupName}' belum terdaftar.";
                    continue;
                }

                if (!$group->is_active) {
                    $failed++;
                    $rowWithReason = $row;
                    $rowWithReason[6] = 'Grup kategori status nonaktif.';
                    $failedRows[] = $rowWithReason;
                    $errors[] = "Baris {$rowNumber}: Grup kategori '{$group->name}' sedang dinonaktifkan.";
                    continue;
                }

                // Check Category Duplicate/Matching
                $existingCategory = null;
                if (!empty($categoryCode)) {
                    $existingCategory = WasteCategory::where('code', $categoryCode)->first();
                }

                if ($existingCategory) {
                    $duplicate++;

                    if ($mode === 'insert_only') {
                        $failed++;
                        $rowWithReason = $row;
                        $rowWithReason[6] = "Kode kategori '{$categoryCode}' sudah terdaftar (Insert Only).";
                        $failedRows[] = $rowWithReason;
                        $errors[] = "Baris {$rowNumber}: Kode kategori '{$categoryCode}' sudah terdaftar.";
                        continue;
                    } elseif ($mode === 'skip_duplicate') {
                        $skipped++;
                        continue;
                    } elseif ($mode === 'insert_or_update') {
                        $updateData = [
                            'name' => $categoryName,
                            'unit' => $unit,
                            'waste_category_group_id' => $group->id,
                            'category_group' => $group->name,
                        ];
                        if ($memberPrice !== null) {
                            $updateData['member_price'] = $memberPrice;
                        }
                        $existingCategory->update($updateData);
                        $updated++;
                    }
                } else {
                    // Create New Category
                    $finalCode = $categoryCode;

                    if (empty($finalCode)) {
                        $attempts = 0;
                        do {
                            $finalCode = WasteCategory::generateCode($group);
                            $exists = WasteCategory::where('code', $finalCode)->exists();
                            $attempts++;
                            if ($attempts > 10) {
                                throw new \Exception("Gagal melakukan auto-generate kode kategori karena duplikasi.");
                            }
                        } while ($exists);
                    }

                    // Double check in case of unique violation
                    $duplicateCheck = WasteCategory::where('code', $finalCode)->first();
                    if ($duplicateCheck) {
                        $duplicate++;
                        if ($mode === 'insert_only') {
                            $failed++;
                            $rowWithReason = $row;
                            $rowWithReason[6] = "Kode kategori auto-generate '{$finalCode}' sudah terdaftar.";
                            $failedRows[] = $rowWithReason;
                            $errors[] = "Baris {$rowNumber}: Kode kategori '{$finalCode}' sudah terdaftar.";
                            continue;
                        } elseif ($mode === 'skip_duplicate') {
                            $skipped++;
                            continue;
                        } elseif ($mode === 'insert_or_update') {
                            $updateData = [
                                'name' => $categoryName,
                                'unit' => $unit,
                                'waste_category_group_id' => $group->id,
                                'category_group' => $group->name,
                            ];
                            if ($memberPrice !== null) {
                                $updateData['member_price'] = $memberPrice;
                            }
                            $duplicateCheck->update($updateData);
                            $updated++;
                            continue;
                        }
                    }

                    $createData = [
                        'code' => $finalCode,
                        'name' => $categoryName,
                        'unit' => $unit,
                        'waste_category_group_id' => $group->id,
                        'category_group' => $group->name,
                    ];
                    if ($memberPrice !== null) {
                        $createData['member_price'] = $memberPrice;
                    }
                    WasteCategory::create($createData);
                    $created++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'Terjadi kesalahan sistem saat memproses import: ' . $e->getMessage()]);
        }

        // Save failed rows in session
        if (!empty($failedRows)) {
            session(['waste_category_import_failed_rows' => $failedRows]);
        } else {
            session()->forget('waste_category_import_failed_rows');
        }

        return back()->with('import_result', [
            'total' => $totalRows,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'duplicate' => $duplicate,
            'failed' => $failed,
            'errors' => $errors,
            'has_failed' => !empty($failedRows),
        ]);
    }

    public function downloadFailedRows()
    {
        $failedRows = session('waste_category_import_failed_rows');
        if (empty($failedRows)) {
            return back()->withErrors(['file' => 'Tidak ada baris gagal yang dapat diunduh.']);
        }

        return Excel::download(new WasteCategoryFailedRowsExport($failedRows), 'failed-rows-kategori-sampah.xlsx');
    }
}
