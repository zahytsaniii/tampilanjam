<?php

namespace App\Services;

use Carbon\Carbon;

class PrayerCalculationService
{
    /**
     * Hitung semua jadwal untuk satu tanggal berdasarkan settings (associative array).
     * settings keys yang digunakan (defaults disediakan jika tidak ada):
     *  - hisab_latitude (float)
     *  - hisab_longitude (float)
     *  - hisab_altitude (meter)
     *  - hisab_timezone (string, ex: "Asia/Jakarta")
     *  - hisab_gmt (float)
     *  - hisab_ref (float)           // optional
     *  - hisab_ikhtiyat (float)      // derajat (contoh 0.035)
     *  - hisab_mazhab (1 atau 2)
     *  - hisab_fajr_angle (deg, ex 20)
     *  - hisab_isya_angle (deg, ex 18)
     *  - hisab_imsak (minutes, ex 10)
     *  - hisab_dhuha_angle (deg, ex 4.5)
     *
     * returns array with keys: imsak, subuh, syuruq, dhuha, dzuhur, ashar, maghrib, isya (strings "HH:MM")
     */
    public function calculateForDate(string $dateYmd, array $settings): array
    {
        // --- read settings with defaults ---
        $lat = isset($settings['hisab_latitude']) ? floatval($settings['hisab_latitude']) : 0.0;
        $lon = isset($settings['hisab_longitude']) ? floatval($settings['hisab_longitude']) : 0.0;
        $alt = isset($settings['hisab_altitude']) ? floatval($settings['hisab_altitude']) : 0.0;
        $tz  = $settings['hisab_timezone'] ?? ($settings['timezone'] ?? 'Asia/Jakarta');
        $gmt = isset($settings['hisab_gmt']) ? floatval($settings['hisab_gmt']) : ($settings['gmt'] ?? 7.0);
        $ref = isset($settings['hisab_ref']) ? floatval($settings['hisab_ref']) : ($lon); // fallback
        $ikhtiyat = isset($settings['hisab_ikhtiyat']) ? floatval($settings['hisab_ikhtiyat']) : 0.035;
        $mazhab = isset($settings['hisab_mazhab']) ? intval($settings['hisab_mazhab']) : 1;
        $fajrAngle = isset($settings['hisab_fajr_angle']) ? floatval($settings['hisab_fajr_angle']) : 20.0;
        $isyaAngle  = isset($settings['hisab_isya_angle'])  ? floatval($settings['hisab_isya_angle'])  : 18.0;
        $imsakMinutes = isset($settings['hisab_imsak']) ? floatval($settings['hisab_imsak']) : 10.0;
        $dhuhaAngle = isset($settings['hisab_dhuha_angle']) ? floatval($settings['hisab_dhuha_angle']) : 4.5;
        $solarDay = isset($settings['hisab_solar_day']) ? intval($settings['hisab_solar_day']) : 365;

        // convert ikhtiyat (deg) to minutes buffer: minutes = ikhtiyat * 60
        $ikhtiyatMinutes = $ikhtiyat * 60.0;

        // parse date
        $date = Carbon::createFromFormat('Y-m-d', $dateYmd, $tz);
        $year  = (int)$date->format('Y');
        $month = (int)$date->format('n');
        $day   = (int)$date->format('j');

        // day of year (n)
        $n = (int)$date->dayOfYear;

        // convert degrees <-> radians helpers
        $deg2rad = function($d) { return $d * pi() / 180.0; };
        $rad2deg = function($r) { return $r * 180.0 / pi(); };

        // 1) declination approximation (degrees)
        // delta = 23.45 * sin(360/365 * (n - 81))
        $delta = 23.45 * sin($deg2rad((360.0 / $solarDay) * ($n - 81)));

        // 2) equation of time (minutes) (approx)
        $B = $deg2rad((360.0 / $solarDay) * ($n - 81));
        $EoT = 9.87 * sin(2 * $B) - 7.53 * cos($B) - 1.5 * sin($B); // minutes

        // 3) altitude correction for horizon dip (degrees)
        // dip ≈ 0.0347 * sqrt(height in meters)   [degrees]
        $dip = 0.0347 * sqrt(max(0.0, $alt));

        // For sunrise/sunset use 0.833 deg + dip (refraction + sun radius + dip)
        $sunriseRefraction = 0.833 + $dip;

        // helper: compute hour angle (in hours) for a given sun elevation angle (deg)
        // using formula: cos H = (sin(el) - sin(lat)*sin(delta)) / (cos(lat)*cos(delta))
        $hourAngleHours = function(float $sunElevationDeg, float $latDeg, float $deltaDeg) use ($deg2rad, $rad2deg) {
            $sinEl = sin($deg2rad($sunElevationDeg));
            $sinLat = sin($deg2rad($latDeg));
            $sinDelta = sin($deg2rad($deltaDeg));
            $cosLat = cos($deg2rad($latDeg));
            $cosDelta = cos($deg2rad($deltaDeg));

            $cosH = ($sinEl - $sinLat * $sinDelta) / ($cosLat * $cosDelta);

            // clamp
            if ($cosH > 1) $cosH = 1;
            if ($cosH < -1) $cosH = -1;

            $Hdeg = $rad2deg(acos($cosH)); // degrees
            return $Hdeg / 15.0; // convert degree -> hours (15° per hour)
        };

        // helper: solar noon (local mean time) in hours (0..24)
        // solarNoon = 12 + timezoneOffset - (longitude/15) - EoT/60
        // timezoneOffset = GMT (e.g., 7) -- but here we use $gmt
        $solarNoonHours = 12.0 + ($gmt - ($lon / 15.0)) - ($EoT / 60.0);

        // apply ikhtiyat: we will add ikhtiyatMinutes to times that should be later (e.g., subuh?).
        // We'll apply ikhtiyat as a time buffer added to each prayer time in minutes (positive or negative).
        // In many implementations ikhtiyat is added to fajr subtraction (or as minutes added to all times as safety).
        // Here we add ikhtiyatMinutes to times (i.e., shift times forward) except for subuh where we add
        // to be safe. We'll apply generally as +ikhtiyatMinutes to all times (except terbit which is fixed).
        $ikhtiyat = $ikhtiyatMinutes;

        // compute hour angles:
        // subuh: sun elevation = -fajrAngle  (example -20 deg) -> pass sunElevationDeg = -fajrAngle
        $H_fajr = $hourAngleHours(-$fajrAngle, $lat, $delta);

        // isya
        $H_isya = $hourAngleHours(-$isyaAngle, $lat, $delta);

        // sunrise / sunset use elevation = -sunriseRefraction
        $H_sunrise = $hourAngleHours(-$sunriseRefraction, $lat, $delta);
        $H_sunset  = $H_sunrise; // symmetric

        // dhuha: use sun elevation = dhuhaAngle (positive elevation), but dhuha occurs after sunrise.
        // We compute hour angle for elevation = dhuhaAngle (positive). Use H then time before noon.
        $H_dhuha = $hourAngleHours($dhuhaAngle, $lat, $delta);

        // asr: compute solar elevation for asr using madhab factor
        // formula: angle_asr = atan(1 / (factor + tan(|lat - delta|))) (radians) -> convert to degrees
        $factor = ($mazhab == 2) ? 2.0 : 1.0; // hanafi=2, syafi'i=1
        // note: use absolute value of latitude - declination in radians when computing tangent
        $angleAsrRad = atan(1.0 / ($factor + tan(abs($deg2rad($lat - $delta)))));
        $angleAsrDeg = $rad2deg($angleAsrRad);
        // Now compute hour angle for that elevation:
        $H_asr = $hourAngleHours($angleAsrDeg, $lat, $delta);

        // compute times in hours (local clock)
        $subuhHours = $solarNoonHours - $H_fajr;
        $terbitHours = $solarNoonHours - $H_sunrise;
        $dzuhurHours = $solarNoonHours;
        $asharHours  = $solarNoonHours + $H_asr;
        $maghribHours = $solarNoonHours + $H_sunset;
        $isyaHours   = $solarNoonHours + $H_isya;
        $dhuhaHours   = $solarNoonHours - $H_dhuha;

        // apply ikhtiyat (minutes) and imsak offset
        $subuhHours = $subuhHours + ($ikhtiyat / 60.0);
        $isyaHours  = $isyaHours + ($ikhtiyat / 60.0);
        $maghribHours = $maghribHours + ($ikhtiyat / 60.0);
        $asharHours = $asharHours + ($ikhtiyat / 60.0);
        $dzuhurHours = $dzuhurHours + ($ikhtiyat / 60.0);
        $dhuhaHours = $dhuhaHours + ($ikhtiyat / 60.0);
        $terbitHours = $terbitHours + ($ikhtiyat / 60.0);

        // imsak = subuh - hisab_imsak minutes
        $imsakHours = $subuhHours - ($imsakMinutes / 60.0);

        // helper to convert fractional hours to local time string "H:i"
        $hoursToTimeString = function($hrs, $dateYmd, $timezone) {
            // hrs may be outside 0..24: convert to absolute time by creating date at 00:00 and adding hrs*3600
            $base = Carbon::createFromFormat('Y-m-d H:i:s', $dateYmd . ' 00:00:00', $timezone);
            $seconds = round($hrs * 3600);
            // allow negative or >24h: add seconds
            $t = $base->copy()->addSeconds($seconds);
            return $t->format('H:i');
        };

        // prepare result
        $result = [
            'imsak' => $hoursToTimeString($imsakHours, $dateYmd, $tz),
            'subuh' => $hoursToTimeString($subuhHours, $dateYmd, $tz),
            'syuruq' => $hoursToTimeString($terbitHours, $dateYmd, $tz),
            'dhuha' => $hoursToTimeString($dhuhaHours, $dateYmd, $tz),
            'dzuhur' => $hoursToTimeString($dzuhurHours, $dateYmd, $tz),
            'ashar' => $hoursToTimeString($asharHours, $dateYmd, $tz),
            'maghrib' => $hoursToTimeString($maghribHours, $dateYmd, $tz),
            'isya' => $hoursToTimeString($isyaHours, $dateYmd, $tz),
        ];

        return $result;
    }
}
