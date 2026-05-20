<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberImportController extends Controller
{
    public function showForm()
    {
        return view('members.import');
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['member_code', 'name', 'phone', 'address']);
            fputcsv($handle, ['', 'Budi Santoso', '08123456789', 'Jl Mawar RT 01']);
            fputcsv($handle, ['', 'Siti Aminah', '08122222222', 'Jl Melati RT 02']);
            fputcsv($handle, ['WRG099', 'Dedi Kurniawan', '08133333333', 'Jl Kenanga RT 03']);
            fclose($handle);
        }, 'template-data-warga.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

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
            $code = trim($mapped['member_code'] ?? '');
            $phone = trim($mapped['phone'] ?? '') ?: null;
            $address = trim($mapped['address'] ?? '') ?: null;

            if ($name === '') {
                $errors[] = "Baris {$row}: name wajib diisi.";
                continue;
            }

            // Find existing member
            $existing = null;
            if ($code !== '') {
                $existing = Member::where('member_code', $code)->first();
            }
            if (!$existing && $phone) {
                $existing = Member::where('phone', $phone)->first();
            }
            if (!$existing && !$phone && $address) {
                $existing = Member::where('name', $name)->where('address', $address)->first();
            }

            if ($existing) {
                $updateData = ['name' => $name];
                if ($phone) $updateData['phone'] = $phone;
                if ($address) $updateData['address'] = $address;
                if ($code !== '' && $code !== $existing->member_code) {
                    if (Member::where('member_code', $code)->where('id', '!=', $existing->id)->exists()) {
                        $errors[] = "Baris {$row}: member_code '{$code}' sudah dipakai member lain.";
                        continue;
                    }
                    $updateData['member_code'] = $code;
                }
                $existing->update($updateData);
                $updated++;
            } else {
                if ($code === '') {
                    $code = Member::generateNextCode();
                } elseif (Member::where('member_code', $code)->exists()) {
                    $errors[] = "Baris {$row}: member_code '{$code}' sudah ada.";
                    continue;
                }
                Member::create([
                    'member_code' => $code,
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
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
