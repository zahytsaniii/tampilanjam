<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $settings['mosque_name'] ?? 'Masjid' }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- =====================================================
        OPTIONAL: DEFAULT FONT GLOBAL
    ====================================================== --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
@php
    $activeTheme = $settings['theme'] ?? 'theme1';
@endphp

@if ($activeTheme === 'theme2')
    <link rel="stylesheet" href="{{ asset('css/theme2.css') }}">
@else
    <link rel="stylesheet" href="{{ asset('css/theme1.css') }}">
@endif

</head>
<body>

{{-- =====================================================
    SISTEM VALIDASI LICENSE (GLOBAL)
====================================================== --}}
@if (($settings['license_status'] ?? 'invalid') === 'valid')

    {{-- =================================================
        SWITCH THEME (DARI DATABASE / SETTING)
        theme1 | theme2
    ================================================== --}}
    @php
        $activeTheme = $settings['theme'] ?? 'theme1';
    @endphp

    @if ($activeTheme === 'theme2')
        {{-- =========================
            THEME 2
        ========================== --}}
        <x-theme.theme2
            :settings="$settings"
            :enabled="$enabled"
            :prayer="$prayer"
            :runningTexts="$runningTexts"
            :hariPasaran="$hariPasaran"
            :tanggalMasehi="$tanggalMasehi"
            :hijri="$hijri"
        />
    @else
        {{-- =========================
            THEME 1 (DEFAULT)
        ========================== --}}
        <x-theme.theme1
            :settings="$settings"
            :enabled="$enabled"
            :prayer="$prayer"
            :runningTexts="$runningTexts"
            :hariPasaran="$hariPasaran"
            :tanggalMasehi="$tanggalMasehi"
            :hijri="$hijri"
            :background="$background"
            :mosque="$mosque"
            :date="$date"
        />
    @endif

@else

{{-- =====================================================
    TAMPILAN SAAT LICENSE TIDAK VALID
====================================================== --}}
<div style="
    width:100vw;
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    background:#0a0a0a;
    color:white;
    flex-direction:column;
    font-family:Poppins, sans-serif;
">

    <h1 style="font-size:60px;color:#ff6b6b">DEVICE BELUM AKTIF</h1>
    <p style="font-size:28px;color:#ccc">Silakan aktivasi license terlebih dahulu</p>

    <div id="clock"
         style="font-size:120px;margin-top:20px;color:#00eaff;font-weight:700">
    </div>

</div>

{{-- JAM TETAP JALAN WALAU LICENSE INVALID --}}
<script>
    function updateClock() {
        let now = new Date().toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById("clock").innerHTML = now;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

@endif

</body>
</html>
