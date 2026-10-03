<?php

namespace App\Http\Controllers;

use App\Exports\CollectorPriceTemplateExport;
use App\Models\Collector;
use App\Models\MaterialAlias;
use App\Models\WasteCategory;
use App\Models\WastePrice;
use App\Models\WastePriceHistory;
use App\Services\DeviceTrackingService;
use App\Services\MaterialMappingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CollectorPriceImportController extends Controller
{
    public function __construct(private MaterialMappingService $mappingService) {}

    public function showForm(Collector $collector)
    {
        $step = 'upload';
        return view('bank-sampah.collectors.import', compact('collector', 'step'));
    }

    public function downloadTemplate(Collector $collector)
    {
        $filename = 'template-harga-lapak-' . \Illuminate\Support\Str::slug($collector->name) . '.xlsx';
        return Excel::download(new CollectorPriceTemplateExport(), $filename);
    }

    public function parse(Request $request, Collector $collector)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file.required' => 'Pilih berkas Excel terlebih dahulu.',
            'file.mimes'    => 'Berkas harus berupa file spreadsheet Excel (.xlsx atau .xls).',
            'file.max'      => 'Ukuran berkas tidak boleh melebihi 5 MB.',
        ]);

        $file = $request->file('file');

        try {
            $sheets = Excel::toArray(null, $file);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca berkas Excel. Pastikan format berkas tidak rusak.']);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return back()->withErrors(['file' => 'Berkas Excel kosong atau tidak memiliki data.']);
        }

        $sheetData = $sheets[0];
        $header = array_shift($sheetData);

        if (empty($header)) {
            return back()->withErrors(['file' => 'Header kolom tidak ditemukan pada berkas Excel.']);
        }

        // Detect column indices
        $colMaterial = null;
        $colPrice = null;

        foreach ($header as $idx => $val) {
            $normalized = mb_strtolower(trim((string) $val));
            if ($colMaterial === null && preg_match('/^(jenis barang|material|nama barang|nama material|kategori|nama kategori)$/i', $normalized)) {
                $colMaterial = $idx;
            } elseif ($colPrice === null && preg_match('/^(harga lapak|harga|collector_price|harga agregator|harga jual)$/i', $normalized)) {
                $colPrice = $idx;
            }
        }

        // Fallback for minimal 2-column format without exact header match
        if ($colMaterial === null && $colPrice === null && count($header) >= 2) {
            $colMaterial = 0;
            $colPrice = 1;
        }

        if ($colMaterial === null || $colPrice === null) {
            return back()->withErrors(['file' => 'Kolom wajib "Jenis Barang" dan "Harga Lapak" tidak dapat dideteksi dalam berkas. Gunakan template yang disediakan.']);
        }

        // Group rows by unique material name
        $uniqueRows = [];
        foreach ($sheetData as $row) {
            if (!isset($row[$colMaterial]) || trim((string) $row[$colMaterial]) === '') {
                continue; // skip empty rows
            }

            $rawName = trim((string) $row[$colMaterial]);
            $rawPriceVal = isset($row[$colPrice]) ? (string) $row[$colPrice] : '';
            
            // Normalize numeric price (strip currency symbols and formatting)
            $cleanPrice = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $rawPriceVal));
            if (!is_numeric($cleanPrice) || (float) $cleanPrice < 0) {
                continue; // skip invalid or negative prices
            }

            $normKey = mb_strtolower($rawName);
            $uniqueRows[$normKey] = [
                'raw_name'     => $rawName,
                'import_price' => (float) $cleanPrice,
            ];
        }

        if (empty($uniqueRows)) {
            return back()->withErrors(['file' => 'Tidak ada baris material dan harga yang valid dalam berkas Excel.']);
        }

        // Fetch existing prices for this collector
        $existingPrices = WastePrice::where('collector_id', $collector->id)
            ->get()
            ->keyBy('waste_category_id');

        $categories = WasteCategory::with('wasteCategoryGroup')->orderBy('name')->get();
        $categoriesById = $categories->keyBy('id');

        // Resolve each unique material: exact match -> alias -> needs mapping (NO FUZZY)
        $items = [];
        $index = 0;

        foreach ($uniqueRows as $row) {
            $rawName = $row['raw_name'];
            $importPrice = $row['import_price'];

            // 1. Exact canonical match
            $matchedCategory = $this->mappingService->findCanonical($rawName);

            // 2. Existing scoped alias match
            if (!$matchedCategory) {
                $alias = $this->mappingService->findAlias($rawName, MaterialAlias::SOURCE_AGGREGATOR, $collector->id);
                if ($alias && $alias->is_active && $alias->wasteCategory) {
                    $matchedCategory = $alias->wasteCategory;
                }
            }

            $currentPrice = null;
            $status = 'NEEDS_MAPPING';
            $categoryId = null;
            $categoryName = null;
            $categoryUnit = null;

            if ($matchedCategory) {
                $categoryId = $matchedCategory->id;
                $categoryName = $matchedCategory->name;
                $categoryUnit = $matchedCategory->unit ?? 'kg';

                if (isset($existingPrices[$categoryId])) {
                    $currentPrice = (float) $existingPrices[$categoryId]->collector_price;
                    if (abs($currentPrice - $importPrice) > 0.001) {
                        $status = 'CHANGED';
                    } else {
                        $status = 'UNCHANGED';
                    }
                } else {
                    $status = 'NEW';
                }
            }

            $items[] = [
                'index'         => $index++,
                'raw_name'      => $rawName,
                'import_price'  => $importPrice,
                'category_id'   => $categoryId,
                'category_name' => $categoryName,
                'category_unit' => $categoryUnit,
                'current_price' => $currentPrice,
                'status'        => $status,
            ];
        }

        $token = 'cpi_' . uniqid();
        session(["collector_price_import_{$token}" => [
            'collector_id' => $collector->id,
            'items'        => $items,
        ]]);

        $step = 'review';

        return view('bank-sampah.collectors.import', compact('collector', 'step', 'items', 'categories', 'token', 'existingPrices'));
    }

    public function commit(Request $request, Collector $collector)
    {
        $token = $request->input('token');
        $sessionData = session("collector_price_import_{$token}");

        if (!$sessionData || $sessionData['collector_id'] !== $collector->id) {
            return redirect()->route('bank-sampah.collectors.import', $collector)
                ->withErrors(['file' => 'Sesi import telah kedaluwarsa. Silakan unggah berkas kembali.']);
        }

        $sessionItems = $sessionData['items'];
        $mappings = $request->input('mappings', []);

        // Validate that all materials are mapped to master materials
        $categories = WasteCategory::all()->keyBy('id');
        $resolvedPayload = [];

        foreach ($sessionItems as $item) {
            $idx = $item['index'];
            $catId = $mappings[$idx] ?? $item['category_id'] ?? null;

            if (empty($catId) || !isset($categories[$catId])) {
                return back()->withErrors(['commit' => "Material '{$item['raw_name']}' belum terhubung dengan Master Material. Selesaikan pemetaan sebelum menyimpan."])->withInput();
            }

            $resolvedPayload[] = [
                'raw_name'     => $item['raw_name'],
                'category_id'  => (int) $catId,
                'import_price' => (float) $item['import_price'],
            ];
        }

        $device = $request->attributes->get('admin_device') 
            ?? app(DeviceTrackingService::class)->getDeviceFromRequest($request, $request->user());

        $newCount = 0;
        $changedCount = 0;
        $unchangedCount = 0;

        DB::transaction(function () use ($collector, $resolvedPayload, $categories, $device, &$newCount, &$changedCount, &$unchangedCount) {
            $existingPrices = WastePrice::where('collector_id', $collector->id)
                ->get()
                ->keyBy('waste_category_id');

            foreach ($resolvedPayload as $row) {
                $catId = $row['category_id'];
                $importPrice = $row['import_price'];
                $cat = $categories->get($catId);
                $unit = $cat?->unit ?? 'kg';

                if (!isset($existingPrices[$catId])) {
                    // Create new WastePrice
                    $price = WastePrice::create([
                        'collector_id'      => $collector->id,
                        'waste_category_id' => $catId,
                        'collector_price'   => $importPrice,
                    ]);

                    WastePriceHistory::create([
                        'waste_price_id'    => $price->id,
                        'collector_id'      => $collector->id,
                        'waste_category_id' => $catId,
                        'old_price'         => null,
                        'new_price'         => $importPrice,
                        'unit'              => $unit,
                        'changed_at'        => now(),
                        'changed_by_user_id'=> auth()->id(),
                        'admin_device_id'   => $device?->id,
                        'source'            => WastePriceHistory::SOURCE_IMPORT,
                    ]);

                    $newCount++;
                } else {
                    $existingPrice = $existingPrices[$catId];
                    $oldPrice = (float) $existingPrice->collector_price;
                    $newPrice = (float) $importPrice;

                    if (abs($oldPrice - $newPrice) > 0.001) {
                        $existingPrice->update([
                            'collector_price' => $newPrice,
                        ]);

                        WastePriceHistory::create([
                            'waste_price_id'    => $existingPrice->id,
                            'collector_id'      => $collector->id,
                            'waste_category_id' => $catId,
                            'old_price'         => $oldPrice,
                            'new_price'         => $newPrice,
                            'unit'              => $unit,
                            'changed_at'        => now(),
                            'changed_by_user_id'=> auth()->id(),
                            'admin_device_id'   => $device?->id,
                            'source'            => WastePriceHistory::SOURCE_IMPORT,
                        ]);

                        $changedCount++;
                    } else {
                        // Price is identical: do NOT insert history
                        $unchangedCount++;
                    }
                }

                // Register alias if raw name differs from master category name
                $normRaw = mb_strtolower(trim($row['raw_name']));
                $normCat = mb_strtolower(trim($cat->name));
                if ($normRaw !== $normCat) {
                    MaterialAlias::updateOrCreate(
                        [
                            'source_type'     => MaterialAlias::SOURCE_AGGREGATOR,
                            'source_id'       => $collector->id,
                            'normalized_name' => $normRaw,
                        ],
                        [
                            'external_name'    => $row['raw_name'],
                            'waste_category_id'=> $catId,
                            'is_active'        => true,
                        ]
                    );
                }
            }
        });

        // Forget import session
        session()->forget("collector_price_import_{$token}");

        return redirect()->route('bank-sampah.collectors.show', $collector)
            ->with('success', "Import harga lapak berhasil. {$newCount} harga baru ditambahkan, {$changedCount} harga diperbarui, dan {$unchangedCount} harga tidak berubah.");
    }
}
