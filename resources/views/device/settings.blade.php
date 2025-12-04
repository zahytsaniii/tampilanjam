@extends('layouts.admin')

@section('content')
<div class="container">

    <h2 class="mb-4">Device Settings</h2>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- =========================
    INFO DEVICE
    ========================== --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">Informasi Device</div>
        <div class="card-body">
            <p><b>Nama Masjid:</b> {{ $settings['mosque_name'] ?? '-' }}</p>
            <p><b>Device ID:</b> {{ $settings['device_id'] ?? '-' }}</p>
            <p><b>Status License:</b>
                @if(($settings['license_status'] ?? 'invalid') == 'valid')
                    <span class="badge bg-success">VALID</span>
                @else
                    <span class="badge bg-danger">TIDAK VALID</span>
                @endif
            </p>

            {{-- TAMPILKAN HANYA JIKA LICENSE VALID --}}
            @if(($settings['license_status'] ?? 'invalid') == 'valid')
                <p class="mt-2">
                    <b>License Expired:</b>
                    <span class="badge bg-warning text-dark">
                        {{ \Carbon\Carbon::parse($settings['license_expired_at'])->format('d M Y') }}
                    </span>
                </p>
            @endif
        </div>
    </div>

    {{-- =========================
    AKTIVASI LICENSE
    ========================== --}}
    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">Aktivasi License</div>
        <div class="card-body">
            <form method="POST" action="{{ route('device.license') }}">
                @csrf

                <div class="mb-3">
                    <label>License Key</label>
                    <input type="text" name="license_key"
                        value="{{ $settings['license_key'] ?? '' }}"
                        class="form-control" required>
                </div>

                <button class="btn btn-success">Aktifkan License</button>
            </form>
        </div>
    </div>

    {{-- =========================
    GANTI PASSWORD
    ========================== --}}
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">Ganti Password</div>
        <div class="card-body">
            <form method="POST" action="{{ route('device.password') }}">
                @csrf

                <div class="mb-3">
                    <label>Password Lama</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="form-control" required>
                </div>

                <button class="btn btn-primary">Simpan Password</button>
            </form>
        </div>
    </div>

    {{-- =========================
    FACTORY RESET
    ========================== --}}
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">Factory Reset</div>
        <div class="card-body">
            <p class="text-danger">
                ⚠️ Semua data jadwal, running text, dan setting akan dihapus!
                <br>Data lisensi & device tetap aman.
            </p>

            <form action="{{ route('device.reset') }}" method="POST"
                onsubmit="return confirm('Yakin ingin reset device? Semua data akan terhapus!')">
                @csrf
                <button class="btn btn-danger">RESET DEVICE</button>
            </form>
        </div>
    </div>

</div>
@endsection
