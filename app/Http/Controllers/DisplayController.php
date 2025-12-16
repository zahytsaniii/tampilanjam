<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PrayerSchedule;
use App\Models\RunningText;
use Carbon\Carbon;
use Alkoumi\LaravelHijriDate\Hijri;
use App\Helpers\DateHelper;
use App\Models\QuranVerse;
use Illuminate\Support\Facades\App;

class DisplayController extends Controller
{
    public function index()
{
    // ✅ Ambil settings
    $settings = Setting::pluck('value', 'key')->toArray();

    // ✅ Timezone
    $timezone = $settings['timezone'] ?? 'Asia/Jakarta';

    // ✅ Tanggal & Waktu Sekarang (JANGAN startOfDay agar hari tidak geser)
    $now = Carbon::now($timezone);
    $date = $now->format('Y-m-d');

    // ✅ Ambil jadwal sholat hari ini
    $prayer = PrayerSchedule::where('date', $date)->first();

    // ✅ Decode jadwal yang ditampilkan
    $enabled = isset($settings['display_enabled_prayers'])
        ? json_decode($settings['display_enabled_prayers'], true)
        : ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

    if (!is_array($enabled)) {
        $enabled = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];
    }

    // ✅ Ambil semua running text aktif
    $runningTexts = RunningText::where('active', true)
        ->pluck('message')
        ->toArray();

    // Ayat Quran
    $quranVerses = QuranVerse::where('active', true)->get();

    // ===============================
    // ✅ HIJRIYAH & PASARAN JAWA
    // ===============================

    // ✅ Toggle
    $dateExtra = isset($settings['display_date_extra'])
        ? json_decode($settings['display_date_extra'], true)
        : [];

    if (!is_array($dateExtra)) {
        $dateExtra = [];
    }

    // ✅ HIJRIYAH
    $hijri = in_array('hijriyah', $dateExtra)
        ? hijriIndo($now)
        : null;

    // ✅ PASARAN JAWA (FIX PALING PENTING ADA DI SINI)
    $pasaran = in_array('pasaran', $dateExtra)
        ? getPasaranJawa($date) // FORMAT: Y-m-d
        : null;

    App::setLocale('id');
    // ✅ NAMA HARI INDONESIA (Sabtu, Minggu, dst)
    $namaHari = $now->isoFormat('dddd');

    // ✅ GABUNG: Sabtu Pahing
    $hariPasaran = $pasaran ? "$namaHari $pasaran" : $namaHari;

    // ✅ TANGGAL FULL: 6 Desember 2025
    $tanggalMasehi = $now->translatedFormat('d F Y');

    // ✅ BACKGROUND DEFAULT (BOLEH DARI SETTING JUGA)
    $background = $settings['display_background'] 
        ?? asset('images/bg-default.jpg');

    // ✅ JAM LIVE (UNTUK JAM BESAR DI TENGAH)
    $clock = $now->format('H:i:s');

    // ✅ NAMA MASJID
    $mosque = $settings['mosque_name'] ?? 'Masjid';

    // ✅ FORMAT TANGGAL UNTUK FOOTER THEME
    $date = $tanggalMasehi; // atau format lain jika mau

    return view('display.index', compact(
        'settings',
        'prayer',
        'enabled',
        'runningTexts',
        'quranVerses',
        'dateExtra',
        'hijri',
        'pasaran',
        'hariPasaran',
        'tanggalMasehi',
        'background',   // ✅ WAJIB DITAMBAHKAN
        'clock',        // ✅ WAJIB DITAMBAHKAN
        'mosque',       // ✅ WAJIB DITAMBAHKAN
        'date'     
    ));
}
}
