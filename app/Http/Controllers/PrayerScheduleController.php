<?php

namespace App\Http\Controllers;

use App\Models\PrayerSchedule;
use App\Models\Setting;
use App\Services\PrayerCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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
    public function generate(Request $request, PrayerCalculationService $calculator)
    {
        // date optional: jika tidak dikirim, gunakan today in timezone settings
        $settings = Setting::pluck('value', 'key')->toArray();
        $tz = $settings['hisab_timezone'] ?? ($settings['timezone'] ?? 'Asia/Jakarta');

        $date = $request->input('date');
        if (!$date) {
            $date = Carbon::now($tz)->format('Y-m-d');
        }

        // pass settings that the service expects (you may prefer to pass all settings)
        $hisabSettings = [
            'hisab_latitude' => $settings['hisab_latitude'] ?? $settings['latitude'] ?? null,
            'hisab_longitude'=> $settings['hisab_longitude'] ?? $settings['longitude'] ?? null,
            'hisab_altitude' => $settings['hisab_altitude'] ?? $settings['height'] ?? 0,
            'hisab_timezone' => $settings['hisab_timezone'] ?? $settings['timezone'] ?? 'Asia/Jakarta',
            'hisab_gmt' => $settings['hisab_gmt'] ?? $settings['gmt'] ?? 7,
            'hisab_ref' => $settings['hisab_ref'] ?? $settings['ref_longitude'] ?? null,
            'hisab_ikhtiyat' => $settings['hisab_ikhtiyat'] ?? $settings['ikhtiyat'] ?? 0.035,
            'hisab_mazhab' => $settings['hisab_mazhab'] ?? $settings['madzhab'] ?? 1,
            'hisab_fajr_angle' => $settings['hisab_fajr_angle'] ?? $settings['sudut_subuh'] ?? 20,
            'hisab_isya_angle'  => $settings['hisab_isya_angle']  ?? $settings['sudut_isya'] ?? 18,
            'hisab_imsak' => $settings['hisab_imsak'] ?? $settings['menit_imsak'] ?? 10,
            'hisab_dhuha_angle' => $settings['hisab_dhuha_angle'] ?? $settings['sudut_dhuha'] ?? 4.5,
            'hisab_solar_day' => $settings['hisab_solar_day'] ?? $settings['solar_day'] ?? 365,
        ];

        $times = $calculator->calculateForDate($date, $hisabSettings);

        // simpan ke DB
        PrayerSchedule::updateOrCreate(
            ['date' => $date],
            [
                'imsak' => $times['imsak'],
                'subuh' => $times['subuh'],
                'syuruq' => $times['syuruq'],
                'dhuha' => $times['dhuha'],
                'dzuhur' => $times['dzuhur'],
                'ashar' => $times['ashar'],
                'maghrib' => $times['maghrib'],
                'isya' => $times['isya'],
            ]
        );

        return back()->with('success', 'Jadwal sholat untuk ' . $date . ' berhasil digenerate.');
    }

    public function generateMonth(Request $request, PrayerCalculationService $service)
    {
        $request->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year'  => 'required|numeric|min:2000|max:2100',
        ]);

        // ✅ Ambil semua setting hisab dari database
        $settings = Setting::pluck('value', 'key')->toArray();

        $month = $request->month;
        $year  = $request->year;

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate   = $startDate->copy()->endOfMonth();

        $count = 0;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {

            $result = $service->calculateForDate(
                $date->format('Y-m-d'),
                $settings
            );

            PrayerSchedule::updateOrCreate(
                ['date' => $date->format('Y-m-d')],
                $result
            );

            $count++;
        }

        return redirect()->back()->with('success', "✅ Berhasil generate $count hari untuk $month-$year");
    }

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
