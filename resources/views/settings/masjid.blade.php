@extends('layouts.admin')

@section('content')

<div class="container my-5">

    <h2 class="text-center mb-4 text-primary">Pengaturan Sistem Masjid</h2>

    {{-- =========================
        NOTIFIKASI GLOBAL
    ========================== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('settings.save') }}" method="POST" class="mt-4" enctype="multipart/form-data">
        @csrf

        {{-- =========================
            PENGATURAN MASJID
        ========================== --}}
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                Pengaturan Masjid
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Nama Masjid</label>
                    <input type="text" name="mosque_name" class="form-control" 
                        value="{{ $settings['mosque_name'] ?? '' }}" placeholder="Masukkan nama masjid">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat Masjid</label>
                    <textarea name="mosque_address" rows="3" class="form-control"
                        placeholder="Masukkan alamat lengkap">{{ $settings['mosque_address'] ?? '' }}</textarea>
                </div>

                <!-- <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" class="form-control"
                            value="{{ $settings['latitude'] ?? '' }}" placeholder="-6.200000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" class="form-control"
                            value="{{ $settings['longitude'] ?? '' }}" placeholder="106.816666">
                    </div>
                </div> -->

                <div class="mb-3">
                    <label class="form-label">Timezone</label>
                    <input type="text" name="timezone" class="form-control"
                        value="{{ $settings['timezone'] ?? 'Asia/Jakarta' }}">
                </div>

            </div>
        </div>

        {{-- =========================
            PENGATURAN TAMPILAN
        ========================== --}}
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                Pengaturan Tampilan Layar
            </div>
            <div class="card-body">

                <!-- <div class="mb-3">
                    <label class="form-label">Running Text</label>
                    <textarea name="running_text" rows="2" class="form-control"
                        placeholder="Teks berjalan yang tampil di layar">{{ $settings['running_text'] ?? '' }}</textarea>
                </div> -->

                <div>
                    <label class="form-label">Jadwal Sholat Yang Ditampilkan</label>

                    @php
                        $selected = isset($settings['display_enabled_prayers']) 
                            ? json_decode($settings['display_enabled_prayers'], true)
                            : [];
                    @endphp

                    <div class="row">
                        @foreach(['imsak','subuh','syuruq','dhuha','dzuhur','ashar','maghrib','isya'] as $sholat)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="display_enabled_prayers[]" 
                                        value="{{ $sholat }}" class="form-check-input"
                                        {{ in_array($sholat, $selected) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ ucfirst($sholat) }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

            </div>
        </div>

        {{-- =========================
            PENGATURAN SUMBER JADWAL
        ========================== --}}
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                Sumber Jadwal Sholat
            </div>
            <div class="card-body">

                @php $source = $settings['schedule_source'] ?? 'api'; @endphp

                <div class="mb-3">
                    <label class="form-label">Sumber Jadwal</label>
                    <select name="schedule_source" id="schedule_source" class="form-select">
                        <option value="api" {{ $source=='api' ? 'selected' : '' }}>API (myquran.com)</option>
                        <option value="import" {{ $source=='import' ? 'selected' : '' }}>Import File</option>
                        <option value="manual" {{ $source=='manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>

                {{-- INPUT API URL --}}
                <div id="api_input" class="{{ $source == 'api' ? '' : 'd-none' }}">
                    {{-- Base URL --}}
                    <label class="form-label fw-bold">Base API URL</label>
                    <input type="text" class="form-control mb-3" value="https://api.myquran.com/v2/" readonly>

                    @php
                        // --- Parse default API URL jika ada ---
                        $apiUrl = $settings['api_url'] ?? '';
                        $defaultCity = '';
                        $defaultMonth = '';
                        $defaultYear = '';

                        if ($apiUrl) {
                            // Contoh URL: https://api.myquran.com/v2/sholat/jadwal/1302/2025/11
                            $parts = explode('/', $apiUrl);
                            $count = count($parts);
                            if($count >= 7){
                                $defaultCity = $parts[$count-3] ?? '';
                                $defaultYear = $parts[$count-2] ?? '';
                                $defaultMonth = $parts[$count-1] ?? '';
                            }
                        }

                        // --- Nama bulan ---
                        $bulanNama = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember'
                        ];
                    @endphp

                    {{-- Pilih Kota --}}
                    <label class="form-label">Pilih Kota</label>
                    <div style="position: relative;"> <!-- Container relatif untuk dropdown -->
                        <input type="text" id="city_input" class="form-control mb-3" placeholder="Ketik nama kota...">
                    </div>
                    <input type="hidden" name="city_id" id="city_id"> {{-- value ID kota untuk API URL --}}

                    {{-- Pilih Bulan --}}
                    <label class="form-label">Bulan</label>
                    <select id="month_select" class="form-select mb-3">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach($bulanNama as $num => $nama)
                            <option value="{{ $num }}" {{ $defaultMonth == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>

                    {{-- Pilih Tahun --}}
                    <label class="form-label">Tahun</label>
                    <select id="year_select" class="form-select mb-3">
                        <option value="">-- Pilih Tahun --</option>
                        @foreach(range(date('Y'), date('Y') + 2) as $y)
                            <option value="{{ $y }}" {{ $defaultYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>

                    {{-- API URL Autogenerate --}}
                    <label class="form-label">API URL Autogenerate</label>
                    <input type="text" name="api_url" id="api_url" class="form-control"
                        value="{{ $settings['api_url'] ?? '' }}" readonly>
                </div>

                {{-- ================= MANUAL HISAB INPUT ================= --}}
                <div id="manual_input" class="mt-4 {{ $source == 'manual' ? '' : 'd-none' }}">
                    <!-- <h5 class="text-primary mb-3">Parameter Perhitungan Jadwal Sholat (Manual / Hisab)</h5> -->
                    <label class="form-label fw-bold">Perhitungan Jadwal Sholat (Manual / Hisab)</label>
                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label>Latitude</label>
                            <input type="text" name="hisab_latitude" class="form-control" value="{{ $settings['hisab_latitude'] ?? '' }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Longitude</label>
                            <input type="text" name="hisab_longitude" class="form-control" value="{{ $settings['hisab_longitude'] ?? '' }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tinggi Tempat (m)</label>
                            <input type="number" name="hisab_altitude" class="form-control" value="{{ $settings['hisab_altitude'] ?? 0 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Timezone</label>
                            <input type="text" name="hisab_timezone" class="form-control" value="{{ $settings['hisab_timezone'] ?? 'Asia/Jakarta' }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>GMT</label>
                            <input type="number" step="0.1" name="hisab_gmt" class="form-control" value="{{ $settings['hisab_gmt'] ?? 7 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Ref (GMT x 15)</label>
                            <input type="number" step="0.01" name="hisab_ref" class="form-control" value="{{ $settings['hisab_ref'] ?? 105 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Ikhtiyat (Derajat)</label>
                            <input type="number" step="0.001" name="hisab_ikhtiyat" class="form-control" value="{{ $settings['hisab_ikhtiyat'] ?? 0.035 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Mazhab</label>
                            <select name="hisab_mazhab" class="form-select">
                                <option value="1" {{ ($settings['hisab_mazhab'] ?? 1) == 1 ? 'selected' : '' }}>Syafi'i</option>
                                <option value="2" {{ ($settings['hisab_mazhab'] ?? 1) == 2 ? 'selected' : '' }}>Hanafi</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Sudut Fajar</label>
                            <input type="number" step="0.1" name="hisab_fajr_angle" class="form-control" value="{{ $settings['hisab_fajr_angle'] ?? 20 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Sudut Isya</label>
                            <input type="number" step="0.1" name="hisab_isya_angle" class="form-control" value="{{ $settings['hisab_isya_angle'] ?? 18 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Menit Imsak</label>
                            <input type="number" step="0.01" name="hisab_imsak" class="form-control" value="{{ $settings['hisab_imsak'] ?? 10 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Sudut Dhuha</label>
                            <input type="number" step="0.1" name="hisab_dhuha_angle" class="form-control" value="{{ $settings['hisab_dhuha_angle'] ?? 4.5 }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Solar Day</label>
                            <input type="number" name="hisab_solar_day" class="form-control" value="{{ $settings['hisab_solar_day'] ?? 365 }}">
                        </div>

                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {

                        const cityInput = document.getElementById("city_input");
                        const cityIdInput = document.getElementById("city_id");
                        let cities = [];
                        let defaultCityId = "{{ $defaultCity }}";

                        // Load daftar kota dari API MyQuran
                        fetch("https://api.myquran.com/v2/sholat/kota/semua")
                            .then(res => res.json())
                            .then(data => {
                                cities = data.data;

                                // Set default kota jika ada
                                if(defaultCityId) {
                                    const defaultCity = cities.find(c => c.id == defaultCityId);
                                    if(defaultCity){
                                        cityInput.value = defaultCity.lokasi;
                                        cityIdInput.value = defaultCity.id;
                                        updateApiUrl();
                                    }
                                }
                            });

                        // --- Autocomplete ---
                        cityInput.addEventListener('input', function () {
                            const term = this.value.toLowerCase();
                            const list = cities.filter(c => c.lokasi.toLowerCase().includes(term));

                            // Hapus dropdown lama
                            let oldDropdown = document.getElementById('city_dropdown');
                            if(oldDropdown) oldDropdown.remove();

                            if(list.length === 0) return;

                            const dropdown = document.createElement('div');
                            dropdown.id = 'city_dropdown';
                            dropdown.style.position = 'absolute';
                            dropdown.style.top = cityInput.offsetHeight + 'px'; // tepat di bawah input
                            dropdown.style.left = '0';
                            dropdown.style.width = cityInput.offsetWidth + 'px';
                            dropdown.style.border = '1px solid #ccc';
                            dropdown.style.background = '#fff';
                            dropdown.style.maxHeight = '200px';
                            dropdown.style.overflowY = 'auto';
                            dropdown.style.zIndex = 1000;

                            list.forEach(c => {
                                const item = document.createElement('div');
                                item.style.padding = '5px';
                                item.style.cursor = 'pointer';
                                item.textContent = c.lokasi;
                                item.addEventListener('click', function () {
                                    cityInput.value = c.lokasi;
                                    cityIdInput.value = c.id;
                                    dropdown.remove();
                                    updateApiUrl();
                                });
                                dropdown.appendChild(item);
                            });

                            cityInput.parentNode.appendChild(dropdown); // parent relatif
                        });

                        // Tutup dropdown kalau klik di luar
                        document.addEventListener('click', function(e){
                            if(e.target !== cityInput){
                                let oldDropdown = document.getElementById('city_dropdown');
                                if(oldDropdown) oldDropdown.remove();
                            }
                        });

                        // --- Generate API URL ---
                        function updateApiUrl() {
                            const cityId = cityIdInput.value;
                            const year = document.getElementById("year_select").value;
                            const month = document.getElementById("month_select").value;

                            if(cityId && year && month){
                                document.getElementById("api_url").value = `https://api.myquran.com/v2/sholat/jadwal/${cityId}/${year}/${month}`;
                            }
                        }

                        document.getElementById("year_select").addEventListener("change", updateApiUrl);
                        document.getElementById("month_select").addEventListener("change", updateApiUrl);

                    });

                    document.getElementById('schedule_source').addEventListener('change', function () {
                        const apiInput = document.getElementById('api_input');
                        const manualInput = document.getElementById('manual_input');
                        if (this.value === 'api') {
                            apiInput.classList.remove('d-none');
                            manualInput.classList.add('d-none');
                        } 
                        else if (this.value === 'manual') {
                            apiInput.classList.add('d-none');
                            manualInput.classList.remove('d-none');
                        } 
                        else {
                            apiInput.classList.add('d-none');
                            manualInput.classList.add('d-none');
                        }
                    });
                </script>

            </div>
        </div>

        {{-- =========================
            PENGATURAN IQOMAH
        ========================== --}}
        <div class="card mb-4 border-secondary">
            <div class="card-header bg-secondary text-white">
                Pengaturan Waktu Iqomah
            </div>
            <div class="card-body">

                <div class="row">
                    @php
                        $iq = [
                            'iqomah_subuh',
                            'iqomah_dzuhur',
                            'iqomah_ashar',
                            'iqomah_maghrib',
                            'iqomah_isya',
                        ];
                    @endphp

                    @foreach($iq as $i)
                        <div class="col-md-2 mb-3">
                            <label class="form-label">{{ ucwords(str_replace('_',' ', $i)) }} (menit)</label>
                            <input type="number" name="{{ $i }}" class="form-control" 
                                value="{{ $settings[$i] ?? 10 }}">
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        {{-- =========================
            PENGATURAN AUDIO ADZAN
        ========================== --}}
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                Pengaturan Audio Otomatis
            </div>
            <div class="card-body">

                <div class="row">

                    {{-- TOGGLE AUDIO --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Aktifkan Audio</label>
                        <select name="audio_enabled" class="form-select">
                            <option value="1" {{ ($settings['audio_enabled'] ?? 0) == 1 ? 'selected' : '' }}>ON</option>
                            <option value="0" {{ ($settings['audio_enabled'] ?? 0) == 0 ? 'selected' : '' }}>OFF</option>
                        </select>
                    </div>

                    {{-- OFFSET WAKTU --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Putar Audio (menit sebelum adzan)</label>
                        <select name="audio_offset_minutes" class="form-select">
                            <option value="10" {{ ($settings['audio_offset_minutes'] ?? 10) == 10 ? 'selected' : '' }}>10 Menit</option>
                            <option value="20" {{ ($settings['audio_offset_minutes'] ?? 20) == 20 ? 'selected' : '' }}>20 Menit</option>
                        </select>
                    </div>

                    {{-- FILE AUDIO --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Upload Audio Adzan (MP3)</label>
                        <input type="file" name="audio_file" class="form-control" accept=".mp3">
                    </div>

                </div>

                {{-- PREVIEW AUDIO --}}
                @if(!empty($settings['audio_file']))
                    <div class="mt-3">
                        <label class="form-label">Preview Audio:</label>
                        <audio controls class="w-100">
                            <source src="{{ asset($settings['audio_file']) }}" type="audio/mpeg">
                        </audio>
                    </div>
                @endif

            </div>
        </div>

        {{-- =========================
            TOMBOL SIMPAN
        ========================== --}}
        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">Simpan Pengaturan</button>
        </div>

    </form>

</div>

@endsection
