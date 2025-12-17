<!DOCTYPE html>
<html>
<head>
    <title>{{ $settings['mosque_name'] ?? 'Masjid' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/theme2.css') }}">
</head>

<body>

@if(($settings['license_status'] ?? 'invalid') == 'valid')

    <div class="header">
        <h1>{{ $settings['mosque_name'] ?? '' }}</h1>
        <p>{{ $settings['mosque_address'] ?? '' }}</p>

        <div class="date-info">
            {{ $hariPasaran }}, {{ $tanggalMasehi }}
        </div>

        @if(!empty($hijri))
            <div class="date-info">
                {{ $hijri }}
            </div>
        @endif

        <div class="clock" id="clock"></div>
        <div class="countdown" id="countdown">Menghitung...</div>
    </div>

    <div class="card">
        <table id="prayerTable">
            @foreach($enabled as $p)
            <tr data-prayer="{{ $p }}">
                <td style="text-transform: capitalize;">{{ $p }}</td>
                <td>
                    @php
                        $t = $prayer[$p] ?? null;
                        if ($t && is_string($t) && str_contains($t, ':')) {
                            $parts = explode(':', $t);
                            echo sprintf('%02d:%02d', $parts[0], $parts[1] ?? 0);
                        } else {
                            echo '--:--';
                        }
                    @endphp
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="running-text">
        <span class="marquee">
            @foreach($runningTexts as $text)
                {{ $text }} &nbsp;&nbsp; • &nbsp;&nbsp;
            @endforeach
        </span>
    </div>

    {{-- ✅ AUDIO ELEMENT SAJA --}}
    @if(($settings['audio_enabled'] ?? 0) == 1)
    <audio id="adzanAudio" preload="auto">
        <source src="{{ asset($settings['audio_file'] ?? 'audio/adzan.mp3') }}" type="audio/mpeg">
    </audio>
    @endif

@else
<div class="header">
    <h1>DEVICE BELUM AKTIF</h1>
    <p>Silakan aktivasi license terlebih dahulu</p>
    <div class="clock" id="clock" style="font-size:120px; color:#ff6b6b"></div>
</div>
@endif


{{-- =========================
   ✅ DATA DARI BLADE UNTUK JS GLOBAL
========================= --}}
<script>
window.PRAYER_TIMES = @json($prayer ?? []);

window.IQOMAH = {
    subuh: {{ $settings['iqomah_subuh'] ?? 10 }},
    dzuhur: {{ $settings['iqomah_dzuhur'] ?? 7 }},
    ashar: {{ $settings['iqomah_ashar'] ?? 5 }},
    maghrib: {{ $settings['iqomah_maghrib'] ?? 5 }},
    isya: {{ $settings['iqomah_isya'] ?? 10 }},
};

window.AUDIO_ENABLED = {{ ($settings['audio_enabled'] ?? 0) == 1 ? 'true' : 'false' }};
window.AUDIO_OFFSET = {{ intval($settings['audio_offset_minutes'] ?? 10) }};
</script>


{{-- ✅ LOAD SATU SCRIPT GLOBAL UNTUK SEMUA THEME --}}
<script src="{{ asset('js/prayer-display.js') }}"></script>

</body>
</html>
