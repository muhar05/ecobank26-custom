<?php

namespace App\Http\Controllers;

use App\Models\WasteCategory;
use Illuminate\Http\Request;

class WasteCategoryImportController extends Controller
{
    public function showForm()
    {
        return view('bank-sampah.waste-categories.import');
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'unit']);
            fputcsv($handle, ['Botol Putih Bersih', 'kg']);
            fputcsv($handle, ['Botol Putih Kotor', 'kg']);
            fputcsv($handle, ['Kardus Bersih', 'kg']);
            fputcsv($handle, ['Gelas Plastik Bersih', 'kg']);
            fputcsv($handle, ['Kertas Campur', 'kg']);
            fclose($handle);
        }, 'template-kategori-sampah.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Gagal membaca file.']);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['file' => 'File kosong.']);
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), $header);
        if (!in_array('name', $header)) {
            fclose($handle);
            return back()->withErrors(['file' => 'Kolom "name" wajib ada di header CSV.']);
        }

        $created = 0;
        $updated = 0;
        $errors = [];
        $row = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            $mapped = array_combine($header, array_pad($data, count($header), ''));
            $name = trim($mapped['name'] ?? '');
            $unit = trim($mapped['unit'] ?? '') ?: 'kg';

            if ($name === '') {
                $errors[] = "Baris {$row}: name wajib diisi.";
                continue;
            }

            $existing = WasteCategory::where('name', $name)->first();
            if ($existing) {
                $existing->update(['unit' => $unit]);
                $updated++;
            } else {
                WasteCategory::create(['name' => $name, 'unit' => $unit]);
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
