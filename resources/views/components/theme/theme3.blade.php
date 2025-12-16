@php
$times = $prayer ? $prayer->toArray() : [];

$labels = [
    'imsak'   => 'Imsak',
    'subuh'   => 'Subuh',
    'syuruq'  => 'Syuruq',
    'dhuha'   => 'Dhuha',
    'dzuhur'  => 'Dzuhur',
    'ashar'   => 'Ashar',
    'maghrib' => 'Maghrib',
    'isya'    => 'Isya',
];

$labels = array_filter($labels, function ($key) use ($enabled) {
    return in_array($key, $enabled);
}, ARRAY_FILTER_USE_KEY);
@endphp

<div class="tv3-container" style="background-image: url('{{ asset('images/bg-masjid.jpg') }}')">

    {{-- ===================== TOP BAR ===================== --}}
    <div class="tv3-topbar">

        {{-- JAM KIRI --}}
        <div class="tv3-clock" id="clock">00:00</div>

        {{-- NAMA MASJID & ALAMAT (TENGAH) --}}
        <div class="tv3-center">
            <div class="tv3-mosque">{{ $mosque }}</div>
            <div class="tv3-address">{{ $settings['mosque_address'] ?? 'Alamat Masjid' }}</div>
        </div>

        {{-- TANGGAL KANAN --}}
        <div class="tv3-right">
            <div class="tv3-hijri">{{ $hijri }}</div>
            <div class="tv3-date">{{ $hariPasaran }}, {{ $tanggalMasehi }}</div>
        </div>

    </div>

    {{-- ===================== JADWAL SHOLAT TV ===================== --}}
    <div class="tv3-prayer-bar">
        @foreach ($labels as $key => $label)
            <div class="tv3-prayer-card" data-prayer="{{ $key }}">
                <div class="tv3-prayer-name">{{ $label }}</div>
                <div class="tv3-prayer-time">{{ $times[$key] ?? '--:--' }}</div>

                {{-- ✅ COUNTDOWN MUNCUL DI SINI SAAT AKTIF --}}
                <div class="tv3-countdown"></div>
            </div>
        @endforeach
    </div>

    {{-- ===================== RUNNING TEXT ===================== --}}
    <div class="tv3-running">
        <marquee>
            @if(count($runningTexts))
                {{ implode(' ••• ', $runningTexts) }}
            @else
                Tidak ada pengumuman hari ini
            @endif
        </marquee>
    </div>

</div>

    {{-- ✅ AUDIO ELEMENT SAJA --}}
    @if(($settings['audio_enabled'] ?? 0) == 1)
    <audio id="adzanAudio" preload="auto">
        <source src="{{ asset($settings['audio_file'] ?? 'audio/adzan.mp3') }}" type="audio/mpeg">
    </audio>
    @endif

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
    window.AUDIO_OFFSET  = {{ intval($settings['audio_offset_minutes'] ?? 10) }};
    </script>

    <script src="{{ asset('js/prayer-display.js') }}"></script>
