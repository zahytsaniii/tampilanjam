<?php

namespace App\Http\Controllers;

use App\Models\PrayerSchedule;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class PrayerScheduleController extends Controller
{
    public function index()
    {
        $scheduleMode = Setting::getValue('schedule_source', 'manual');

        $schedules = PrayerSchedule::orderBy('date','asc')->get();

        return view('schedule.index', [
            'scheduleMode' => $scheduleMode,
            'schedules'    => $schedules,
        ]);
    }

    /* =====================================================
        1) AMBIL JADWAL DARI API
    =======================================================*/
    public function fetchFromAPI()
    {
        $apiUrl = Setting::getValue('api_url');

        if (!$apiUrl) {
            return back()->with('error', 'API URL belum diatur di halaman Setting.');
        }

        try {
            $response = Http::get($apiUrl);

            if (!$response->ok()) {
                return back()->with('error', 'Gagal mengambil data dari API.');
            }

            $json = $response->json();

            // Pastikan struktur sesuai
            if (!isset($json['data']['jadwal']) || !is_array($json['data']['jadwal'])) {
                return back()->with('error', 'Format data API tidak sesuai.');
            }

            $jadwal = $json['data']['jadwal'];

            foreach ($jadwal as $d) {
                PrayerSchedule::updateOrCreate(
                    ['date' => $d['date']],
                    [
                        'imsak'   => $d['imsak'] ?? null,
                        'subuh'   => $d['subuh'] ?? null,
                        'syuruq'  => $d['terbit'] ?? null, // API pakai “terbit”
                        'dhuha'    => $d['dhuha'] ?? null,
                        'dzuhur'  => $d['dzuhur'] ?? null,
                        'ashar'   => $d['ashar'] ?? null,
                        'maghrib' => $d['maghrib'] ?? null,
                        'isya'    => $d['isya'] ?? null,
                    ]
                );
            }

            return back()->with('success', 'Jadwal berhasil diambil dari API.');

        } catch (\Exception $e) {
            return back()->with('error', 'Kesalahan: ' . $e->getMessage());
        }
    }


    /* =====================================================
        2) IMPORT FILE
    =======================================================*/
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:csv,xlsx,xls']
        ]);

        try {
            $data = \Excel::toArray([], $request->file('file'));

            if (!isset($data[0]) || count($data[0]) === 0) {
                return back()->with('error', 'File kosong atau format tidak dikenali.');
            }

            $rows = $data[0];

            foreach ($rows as $i => $row) {
                if ($i === 0) continue; // Skip header

                if (!isset($row[0])) continue; // Lewatkan jika tidak ada tanggal

                PrayerSchedule::updateOrCreate(
                    ['date' => $row[0]],
                    [
                        'imsak'   => $row[1] ?? null,
                        'subuh'   => $row[2] ?? null,
                        'syuruq'  => $row[3] ?? null,
                        'dhuha'    => $row[4] ?? null,
                        'dzuhur'  => $row[5] ?? null,
                        'ashar'   => $row[6] ?? null,
                        'maghrib' => $row[7] ?? null,
                        'isya'    => $row[8] ?? null,
                    ]
                );
            }

            return back()->with('success', 'Jadwal berhasil diimport.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimport file: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $filePath = public_path('templates/prayer_schedule_template.xlsx');

        if (!file_exists($filePath)) {
            return back()->with('error', 'Template tidak ditemukan. Pastikan file tersedia.');
        }

        return response()->download($filePath);
    }

    /* =====================================================
        3) INPUT MANUAL
    =======================================================*/
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|unique:prayer_schedules,date',
            'imsak'   => 'nullable',
            'subuh'   => 'nullable',
            'syuruq'  => 'nullable',
            'dhuha'    => 'nullable',
            'dzuhur'  => 'nullable',
            'ashar'   => 'nullable',
            'maghrib' => 'nullable',
            'isya'    => 'nullable',
        ]);

        PrayerSchedule::create([
            'date'    => $request->date,
            'imsak'   => $request->imsak,
            'subuh'   => $request->subuh,
            'syuruq'  => $request->syuruq,
            'dhuha'    => $request->dhuha,
            'dzuhur'  => $request->dzuhur,
            'ashar'   => $request->ashar,
            'maghrib' => $request->maghrib,
            'isya'    => $request->isya,
        ]);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

}
