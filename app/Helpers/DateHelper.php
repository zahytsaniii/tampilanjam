<?php

if (!function_exists('GregorianToJD')) {
    function GregorianToJD($day, $month, $year)
    {
        if ($month > 2) {
            $month = $month - 3;
        } else {
            $month = $month + 9;
            $year = $year - 1;
        }

        $c  = floor($year / 100);
        $ya = $year - (100 * $c);

        $j  = floor((146097 * $c) / 4);
        $j += floor((1461 * $ya) / 4);
        $j += floor(((153 * $month) + 2) / 5);
        $j += $day + 1721119;

        return $j;
    }
}

if (!function_exists('getPasaranJawa')) {
    function getPasaranJawa($date)
    {
        // Urutan PASARAN YANG BENAR
        $pasaran = ['Legi', 'Pahing', 'Pon', 'Wage', 'Kliwon'];

        // Tanggal acuan resmi: 1970-01-01 = Kamis Legi
        $baseDate = \Carbon\Carbon::create(1970, 1, 1);
        $targetDate = \Carbon\Carbon::parse($date);

        $diffDays = $baseDate->diffInDays($targetDate);

        // ✅ OFFSET +4 karena starting point = KLIWON
        $index = ($diffDays + 3) % 5;

        return $pasaran[$index];
    }
}

if (!function_exists('hijriIndo')) {
    function hijriIndo($date)
    {
        $bulan = [
            'Muharram',
            'Safar',
            'Rabiul Awal',
            'Rabiul Akhir',
            'Jumadil Awal',
            'Jumadil Akhir',
            'Rajab',
            'Sya’ban',
            'Ramadhan',
            'Syawal',
            'Dzulqa’dah',
            'Dzulhijjah',
        ];

        $hijri = \Alkoumi\LaravelHijriDate\Hijri::Date('j-n-Y', $date);

        [$tanggal, $bulanIndex, $tahun] = explode('-', $hijri);

        $namaBulan = $bulan[$bulanIndex - 1] ?? '';

        return "{$tanggal} {$namaBulan} {$tahun} H";
    }
}
