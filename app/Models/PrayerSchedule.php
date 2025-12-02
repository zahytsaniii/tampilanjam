<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerSchedule extends Model
{
    protected $fillable = [
        'date',
        'imsak',
        'subuh',
        'syuruq',
        'dhuha',
        'dzuhur',
        'ashar',
        'maghrib',
        'isya',
    ];

    protected $dates = ['date'];

    /**
     * Ambil jadwal hari ini.
     */
    public static function today()
    {
        return static::where('date', now()->toDateString())->first();
    }

    /**
     * Ambil jadwal berdasarkan tanggal tertentu.
     */
    public static function forDate($date)
    {
        return static::where('date', $date)->first();
    }

    /**
     * Ambil jadwal hari ini atau buat kosong.
     */
    public static function todayOrEmpty()
    {
        return static::firstOrNew(['date' => now()->toDateString()]);
    }
}
