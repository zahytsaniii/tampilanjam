/* ===========================
   JAM REALTIME
=========================== */
function updateClock() {
    const el = document.getElementById("clock");
    if (!el) return;

    let now = new Date().toLocaleTimeString('id-ID', { hour12: false });
    el.innerHTML = now;
}
setInterval(updateClock, 1000);
updateClock();


/* ===========================
   DATA GLOBAL DARI BLADE
=========================== */
const prayerTimes = window.PRAYER_TIMES || {};
const iqomah = window.IQOMAH || {};
const audioEnabled = window.AUDIO_ENABLED || false;
const audioOffset = window.AUDIO_OFFSET || 0;


/* ===========================
   NEXT EVENT (SHOLAT / IQOMAH)
=========================== */
function getNextEvent() {
    const now = new Date();
    let upcoming = [];

    if (!window.PRAYER_TIMES) return null;

    Object.keys(window.PRAYER_TIMES).forEach(key => {
        const rawTime = window.PRAYER_TIMES[key];

        // Harus string jam
        if (typeof rawTime !== "string" || !rawTime.includes(":")) return;

        // Support HH:MM & HH:MM:SS
        const parts = rawTime.split(":").map(Number);
        const h = parts[0];
        const m = parts[1] ?? 0;
        const s = parts[2] ?? 0;

        if (isNaN(h) || isNaN(m)) return;

        /* =========================
           SHOLAT
        ========================= */
        let sholatTime = new Date();
        sholatTime.setHours(h, m, s, 0);

        // Sholat boleh digeser ke BESOK
        if (sholatTime <= now) {
            sholatTime.setDate(sholatTime.getDate() + 1);
        }

        upcoming.push({
            name: key,
            type: "sholat",
            time: sholatTime
        });

        /* =========================
           IQOMAH
        ========================= */
        let iq = (window.IQOMAH || {})[key] || 0;
        if (iq > 0) {

            // IQOMAH HARUS dihitung dari waktu ASLI hari ini
            let iqTime = new Date();
            iqTime.setHours(h, m, s, 0);
            iqTime = new Date(iqTime.getTime() + iq * 60000);

            // IQOMAH hanya digeser ke BESOK jika benar-benar lewat
            if (iqTime <= now) {
                iqTime.setDate(iqTime.getDate() + 1);
            }

            upcoming.push({
                name: key,
                type: "iqomah",
                time: iqTime
            });
        }
    });

    upcoming.sort((a, b) => a.time - b.time);
    return upcoming[0] || null;
}



/* ===========================
   COUNTDOWN + HIGHLIGHT
=========================== */
function updateCountdown() {
    const el = document.getElementById("countdown");
    if (!el) return;

    const event = getNextEvent();
    if (!event) {
        el.innerHTML = "Tidak ada jadwal";
        return;
    }

    const now = new Date();
    let diff = Math.floor((event.time - now) / 1000);
    if (diff < 0) diff = 0;

    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    const s = diff % 60;

    const label = event.type === "sholat"
        ? `Menuju waktu ${event.name}`
        : `Menuju Iqomah ${event.name}`;

    el.innerHTML = `${label}: ${h}j ${m}m ${s}d`;

    // ✅ HIGHLIGHT ROW
    document.querySelectorAll("#prayerTable tr").forEach(tr => {
        tr.classList.remove("active-row");
        if (tr.dataset.prayer === event.name) {
            tr.classList.add("active-row");
        }
    });
}

setInterval(updateCountdown, 1000);
updateCountdown();


/* ===========================
   AUDIO ADZAN
=========================== */
if (audioEnabled) {
    let audioPlayed = {};
    let lastDate = new Date().toDateString();
    const audio = document.getElementById('adzanAudio');

    document.addEventListener('click', function () {
        if (!audio) return;
        audio.muted = false;
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
            console.log('✅ Audio unlocked');
        });
    }, { once: true });

    function resetDailyAudio() {
        const today = new Date().toDateString();
        if (today !== lastDate) {
            audioPlayed = {};
            lastDate = today;
        }
    }

    setInterval(() => {
        if (!audio) return;

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
                    console.log('🔊 Adzan:', sholat);
                });
            }
        });

    }, 1000);
}


/* ===========================
   HIGHLIGHT THEME 1
=========================== */
function setActivePrayerTheme1() {
    if (typeof PRAYER_TIMES !== "object") return;

    const now = new Date();
    let closestKey = null;
    let closestDiff = null;

    Object.keys(PRAYER_TIMES).forEach(key => {

        // ✅ FILTER KETAT: hanya waktu sholat valid
        const value = PRAYER_TIMES[key];

        if (
            typeof value !== "string" ||       // harus string
            !value.includes(":") ||            // harus ada :
            value.length < 4                   // minimal HH:M
        ) return;

        // ✅ AMBIL JAM & MENIT SAJA (aman untuk "HH:MM" & "HH:MM:SS")
        const parts = value.split(":");
        const h = parseInt(parts[0]);
        const m = parseInt(parts[1]);

        if (isNaN(h) || isNaN(m)) return;

        const event = new Date();
        event.setHours(h, m, 0, 0);

        // kalau sudah lewat → geser ke besok
        if (event <= now) event.setDate(event.getDate() + 1);

        const diff = event - now;

        if (closestDiff === null || diff < closestDiff) {
            closestDiff = diff;
            closestKey = key;
        }
    });

    // ✅ AKTIFKAN HIGHLIGHT
    document
        .querySelectorAll(".theme1-time-row")
        .forEach(row => {
            row.classList.remove("active");
            if (row.dataset.prayer === closestKey) {
                row.classList.add("active");
            }
        });
}

// Jalankan pertama kali
setActivePrayerTheme1();

// Update setiap 30 detik
setInterval(setActivePrayerTheme1, 30000);


/* ===========================
   HIGHLIGHT THEME 3
=========================== */
document.addEventListener('DOMContentLoaded', function () {

    const VALID_PRAYERS = [
        'imsak',
        'subuh',
        'syuruq',
        'dhuha',
        'dzuhur',
        'ashar',
        'maghrib',
        'isya'
    ];

    // DEBUG OPTIONAL
    console.log('PRAYER_TIMES (raw):', window.PRAYER_TIMES);

    function parseToMinutes(value) {
        if (!value || typeof value !== 'string') return null;

        // HH:MM
        let match = value.match(/^(\d{1,2}):(\d{2})$/);
        if (match) {
            return (parseInt(match[1]) * 60) + parseInt(match[2]);
        }

        // HH:MM:SS
        match = value.match(/^(\d{1,2}):(\d{2}):(\d{2})$/);
        if (match) {
            return (parseInt(match[1]) * 60) + parseInt(match[2]);
        }

        return null;
    }

    function updateTV3Prayer() {
        if (!window.PRAYER_TIMES) return;

        const now = new Date();
        const currentMinutes = now.getHours() * 60 + now.getMinutes();

        const prayerArray = [];

        // ✅ AMBIL HANYA JADWAL SHOLAT VALID
        for (const key of VALID_PRAYERS) {
            const raw = window.PRAYER_TIMES[key];
            const minutes = parseToMinutes(raw);

            if (minutes !== null) {
                prayerArray.push({ key, minutes });
            }
        }

        if (!prayerArray.length) return;

        prayerArray.sort((a, b) => a.minutes - b.minutes);

        let next = prayerArray.find(p => p.minutes > currentMinutes);
        let isTomorrow = false;

        if (!next) {
            next = prayerArray[0];
            next.minutes += 1440; // tambah 24 jam
            isTomorrow = true;
        }

        document.querySelectorAll('.tv3-prayer-card').forEach(card => {
            card.classList.remove('active');
            const cd = card.querySelector('.tv3-countdown');
            if (cd) cd.innerHTML = '';
        });

        const activeCard = document.querySelector(
            `.tv3-prayer-card[data-prayer="${next.key}"]`
        );

        if (!activeCard) return;

        activeCard.classList.add('active');

        const diff = next.minutes - currentMinutes;
        const jam = Math.floor(diff / 60);
        const menit = diff % 60;

        const cdEl = activeCard.querySelector('.tv3-countdown');
        if (cdEl) {
            cdEl.innerHTML = `${isTomorrow ? 'Besok • ' : ''}Menuju Adzan ${jam}j ${menit}m`;
        }

        // OPTIONAL GLOBAL DISPLAY
        const global = document.getElementById('tv3GlobalCountdown');
        if (global) {
            global.innerText = `${isTomorrow ? 'Besok — ' : ''}Menuju ${next.key.toUpperCase()} • ${jam}j ${menit}m`;
        }
    }

    updateTV3Prayer();
    setInterval(updateTV3Prayer, 1000);

});




