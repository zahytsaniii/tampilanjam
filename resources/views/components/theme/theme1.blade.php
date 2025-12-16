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

<div class="theme1-container">

    {{-- ================= PANEL KIRI ================= --}}
    <div class="theme1-sidebar" id="prayerList">
        @foreach ($labels as $key => $label)
            <div class="theme1-time-row" data-prayer="{{ $key }}">
                <span class="label">{{ $label }}</span>
                <span class="time">{{ $times[$key] ?? '--:--' }}</span>
            </div>
        @endforeach
    </div>

    {{-- ================= PANEL UTAMA ================= --}}
    <div class="theme1-main" style="background-image: url('{{ asset('images/bg-masjid.jpg') }}')">

        {{-- HEADER --}}
        <div class="theme1-header">
            <h1>{{ $mosque }}</h1>
            <p>{{ $hariPasaran }} - {{ $date }}</p>
            @if(!empty($hijri))
                <p>{{ $hijri }}</p>
            @endif
        </div>

        {{-- JAM --}}
        <div class="theme1-clock" id="clock"></div>

        {{-- COUNTDOWN --}}
        <div class="theme1-countdown" id="countdown">
            Menghitung...
        </div>

        {{-- ================= AYAT AL-QURAN ================= --}}
        @if(isset($quranVerses) && $quranVerses->count())
            <div class="theme1-quran">
                @foreach($quranVerses as $i => $v)
                    <div class="quran-slide {{ $i === 0 ? 'active' : '' }}">
                        <div class="quran-arabic">{{ $v->arabic_text }}</div>
                        <div class="quran-surah">{{ $v->surah }}</div>
                        <div class="quran-translation">{{ $v->translation }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ================= RUNNING TEXT ================= --}}
        <div class="theme1-running">
            <marquee behavior="scroll" direction="left">
                @if(count($runningTexts))
                    {{ implode(' ••• ', $runningTexts) }}
                @else
                    Tidak ada pengumuman hari ini
                @endif
            </marquee>
        </div>

    </div>

    {{-- ================= AUDIO ================= --}}
    @if(($settings['audio_enabled'] ?? 0) == 1)
    <audio id="adzanAudio" preload="auto">
        <source src="{{ asset($settings['audio_file'] ?? 'audio/adzan.mp3') }}" type="audio/mpeg">
    </audio>
    @endif

    {{-- ================= GLOBAL JS ================= --}}
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

    {{-- ================= QURAN SLIDER JS ================= --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const slides = document.querySelectorAll('.quran-slide');
        let index = 0;

        if (slides.length <= 1) return;

        setInterval(() => {
            slides[index].classList.remove('active');
            index = (index + 1) % slides.length;
            slides[index].classList.add('active');
        }, 12000); // 12 detik / ayat

    });
    </script>

</div>
