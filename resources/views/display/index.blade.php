<!DOCTYPE html>
<html>
<head>
    <title>{{ $settings['mosque_name'] ?? 'Masjid' }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #051428, #0a3d62, #0a1a2a);
            background-size: 300% 300%;
            animation: bgMove 15s ease infinite;
            color: #fff;
            overflow: hidden;
        }

        @keyframes bgMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .header {
            text-align: center;
            padding: 20px 0;
        }

        h1 {
            font-size: 55px;
            font-weight: 800;
            text-shadow: 0 0 15px rgba(255,255,255,0.5);
        }

        p {
            margin-top: -10px;
            color: #bcd;
            font-size: 22px;
        }

        /* CLOCK */
        .clock {
            font-size: 90px;
            font-weight: 700;
            letter-spacing: 3px;
            margin-top: 5px;
            text-shadow: 0 0 18px #00eaff;
        }

        /* COUNTDOWN */
        .countdown {
            font-size: 35px;
            margin-top: -15px;
            color: #9eeaff;
            font-weight: 600;
        }

        /* PRAYER TABLE */
        .card {
            width: 80%;
            margin: 30px auto;
            background: rgba(255,255,255,0.08);
            padding: 20px;
            border-radius: 18px;
            box-shadow: 0 0 25px rgba(0,0,0,0.25);
            backdrop-filter: blur(10px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 32px;
        }

        tr {
            transition: 0.3s;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        /* highlight sholat yg mendekat */
        .active-row {
            background: rgba(0, 255, 255, 0.15);
            box-shadow: 0 0 20px rgba(0, 255, 255, .3);
        }

        /* RUNNING TEXT */
        .running-text {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 18px;
            font-size: 28px;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px);
            overflow: hidden;
            white-space: nowrap;
        }

        .date-info {
            font-size: 26px;
            margin-top: -5px;
            color: #d6ecff;
            font-weight: 500;
            text-shadow: 0 0 8px rgba(255,255,255,0.4);
        }

        .marquee {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 18s linear infinite;
        }

        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>

<body>
@if(($settings['license_status'] ?? 'invalid') == 'valid')
<div class="header">
    <h1>{{ $settings['mosque_name'] ?? '' }}</h1>
    <p>{{ $settings['mosque_address'] ?? '' }}</p>
    <!-- <div class="date-info" id="dateInfo"></div> -->
     <div class="date-info">
        {{ $hariPasaran }}, {{ $tanggalMasehi }}
    </div>
    <div class="date-info">
        {{ $hijri }} 
    </div>

    <div class="clock" id="clock"></div>
    <div class="countdown" id="countdown">Menghitung...</div>
</div>

<div class="card">
    <table id="prayerTable">
        @foreach($enabled as $p)
        <tr data-prayer="{{ $p }}">
            <td style="text-transform: capitalize;">{{ $p }}</td>
            <td>{{ $prayer[$p] ?? '--:--' }}</td>
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

@else
    {{-- =========================
        TAMPILAN SAAT LICENSE TIDAK VALID
    ========================== --}}
    <div class="header">
        <h1>DEVICE BELUM AKTIF</h1>
        <p>Silakan aktivasi license terlebih dahulu</p>
        <div class="clock" id="clock" style="font-size:120px; color:#ff6b6b"></div>
    </div>

@endif

<!-- ================================
     SCRIPT
==================================-->
<script>
    // TANGGAL + HARI
    // function updateDate() {
    //     const bulan = [
    //         "Januari","Februari","Maret","April","Mei","Juni",
    //         "Juli","Agustus","September","Oktober","November","Desember"
    //     ];

    //     const hari = [
    //         "Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"
    //     ];

    //     let now = new Date();
    //     let namaHari = hari[now.getDay()];
    //     let tanggal = now.getDate();
    //     let namaBulan = bulan[now.getMonth()];
    //     let tahun = now.getFullYear();

    //     document.getElementById('dateInfo').innerHTML =
    //         `${namaHari}, ${tanggal} ${namaBulan} ${tahun}`;
    // }

    // updateDate();

    // CLOCK
    function updateClock() {
        let now = new Date().toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById("clock").innerHTML = now;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // COUNTDOWN SHOLAT / IQOMAH
    const prayerTimes = @json($prayer);
    const iqomah = {
        subuh: {{ $settings['iqomah_subuh'] ?? 10 }},
        dzuhur: {{ $settings['iqomah_dzuhur'] ?? 7 }},
        ashar: {{ $settings['iqomah_ashar'] ?? 5 }},
        maghrib: {{ $settings['iqomah_maghrib'] ?? 5 }},
        isya: {{ $settings['iqomah_isya'] ?? 10 }}
    };

    function getNextEvent() {
        let now = new Date();
        let upcoming = [];

        Object.keys(prayerTimes).forEach(key => {
            if (!['date','id','created_at','updated_at'].includes(key)) {
                let [h, m] = (prayerTimes[key] || "99:99").split(":");
                let eventTime = new Date();
                eventTime.setHours(h, m, 0, 0);

                if (eventTime > now) {
                    upcoming.push({ name: key, type: 'sholat', time: eventTime });
                }

                let iq = iqomah[key] || 0;
                let iqTime = new Date(eventTime.getTime() + iq * 60000);
                if (iqTime > now) {
                    upcoming.push({ name: key, type: 'iqomah', time: iqTime });
                }
            }
        });

        upcoming.sort((a, b) => a.time - b.time);
        return upcoming[0];
    }

    // Highlight row + countdown
    function updateCountdown() {
        let event = getNextEvent();
        if (!event) return;

        let now = new Date();
        let diff = (event.time - now) / 1000;

        let h = Math.floor(diff / 3600);
        let m = Math.floor((diff % 3600) / 60);
        let s = Math.floor(diff % 60);

        let label = event.type === 'sholat'
            ? `Menuju waktu ${event.name}`
            : `Menuju Iqomah ${event.name}`;

        document.getElementById("countdown").innerHTML =
            `${label}: ${h}j ${m}m ${s}d`;

        // highlight active prayer
        document.querySelectorAll("#prayerTable tr").forEach(tr => {
            if (tr.dataset.prayer === event.name) {
                tr.classList.add("active-row");
            } else {
                tr.classList.remove("active-row");
            }
        });
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
</script>
    @if(($settings['audio_enabled'] ?? 0) == 1)
        <audio id="adzanAudio" preload="auto">
            <source src="{{ asset($settings['audio_file'] ?? 'audio/adzan.mp3') }}" type="audio/mpeg">
        </audio>

        <script>
        const audioEnabled = true;
        const audioOffset = {{ intval($settings['audio_offset_minutes'] ?? 10) }};
        let audioPlayed = {};
        let lastDate = new Date().toDateString();

        const audio = document.getElementById('adzanAudio');

        // ✅ WAJIB: UNLOCK SEKALI DENGAN 1 KLIK USER
        document.addEventListener('click', function () {
            audio.muted = false;
            audio.play().then(() => {
                audio.pause();
                audio.currentTime = 0;
                console.log('✅ Audio berhasil di-unlock oleh user');
            }).catch(e => console.warn('❌ Unlock gagal:', e));
        }, { once: true });

        // ✅ RESET SETIAP HARI
        function resetDailyAudio() {
            const today = new Date().toDateString();
            if (today !== lastDate) {
                audioPlayed = {};
                lastDate = today;
            }
        }

        // ✅ SISTEM AUDIO OTOMATIS BERDASARKAN OFFSET
        setInterval(() => {
            if (!audioEnabled || !audio) return;

            const now = new Date();
            resetDailyAudio();

            ['subuh','dzuhur','ashar','maghrib','isya'].forEach(sholat => {
                if (!prayerTimes[sholat]) return;

                const [h, m] = prayerTimes[sholat].split(':').map(Number);

                const target = new Date();
                target.setHours(h);
                target.setMinutes(m);
                target.setSeconds(0);

                const offsetTarget = new Date(target.getTime() - (audioOffset * 60000));
                const key = sholat + '-' + now.toDateString();

                if (
                    now.getHours() === offsetTarget.getHours() &&
                    now.getMinutes() === offsetTarget.getMinutes() &&
                    !audioPlayed[key]
                ) {
                    audio.play().then(() => {
                        audioPlayed[key] = true;
                        console.log('🔊 Memutar audio:', sholat);
                    }).catch(err => {
                        console.warn('❌ Audio gagal play:', err);
                    });
                }
            });

        }, 1000);
        </script>
    @endif
</body>
</html>
