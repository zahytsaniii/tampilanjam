<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PrayerSchedule;
use App\Models\RunningText;
use Carbon\Carbon;

class DisplayController extends Controller
{
    public function index()
    {
        // Ambil settings
        $settings = Setting::pluck('value', 'key')->toArray();

        // Tanggal hari ini
        $date = Carbon::now($settings['timezone'] ?? 'Asia/Jakarta')->format('Y-m-d');

        // Ambil jadwal sholat hari ini
        $prayer = PrayerSchedule::where('date', $date)->first();

        // Decode jadwal yang ditampilkan
        $enabled = isset($settings['display_enabled_prayers'])
            ? json_decode($settings['display_enabled_prayers'], true)
            : ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];

        if (!is_array($enabled)) {
            $enabled = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];
        }

        // Ambil semua running text aktif
        $runningTexts = RunningText::where('active', true)->pluck('message')->toArray();

        return view('display.index', compact('settings', 'prayer', 'enabled', 'runningTexts'));
    }
}
