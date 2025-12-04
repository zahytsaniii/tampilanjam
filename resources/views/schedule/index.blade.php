@extends('layouts.admin')

@section('content')

<div class="container">

    <h2 class="mb-4">Jadwal Sholat</h2>

    {{-- ===================================================================
        NOTIFIKASI GLOBAL (SUCCESS / ERROR)
    ===================================================================== --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mt-2 mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="alert alert-info">
        Mode Pengambilan Jadwal:
        <strong>{{ strtoupper($scheduleMode) }}</strong>
    </div>


    {{-- ===================================================================
        MODE 1: API
    ===================================================================== --}}
    @if ($scheduleMode === 'api')
        <form action="{{ route('schedule.fetch-api') }}" method="POST">
            @csrf
            <button class="btn btn-primary">Ambil Jadwal dari API</button>
        </form>

        {{-- Info tambahan ketika tidak ada koneksi API --}}
        <div class="mt-3 alert alert-warning">
            Jika API gagal diambil, sistem akan menampilkan pesan error otomatis.
        </div>
    @endif


    {{-- ===================================================================
        MODE 2: IMPORT FILE
    ===================================================================== --}}
    @if ($scheduleMode === 'import')
        <form action="{{ route('schedule.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label>Upload File Excel/CSV</label>
            <input type="file" name="file" class="form-control mb-2" required>

            <button class="btn btn-success">Import Jadwal</button>

            <a href="{{ route('schedule.template') }}" class="btn btn-info mt-2">
                Download Template
            </a>
        </form>

        <div class="alert alert-warning mt-2">
            Pastikan format file sudah benar. Jika format salah, sistem akan menampilkan error.
        </div>
    @endif


    {{-- ===================================================================
        MODE 3: MANUAL INPUT
    ===================================================================== --}}
    @if ($scheduleMode === 'manual')
        <form action="{{ route('schedule.generate-month') }}" method="POST" class="mt-3">
            @csrf

            <div class="row">
                <div class="col-md-3">
                    <label>Bulan</label>
                    <select name="month" class="form-control" required>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Tahun</label>
                    <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                </div>
            </div>

            <button class="btn btn-primary mt-3">
                🔄 Generate Jadwal 1 Bulan
            </button>
        </form>

        <div class="alert alert-info mt-2">
            Jadwal akan dihitung otomatis berdasarkan parameter hisab di menu <b>Settings</b>.
        </div>
    @endif


    <hr>

    
    {{-- ===================================================================
        TABEL DATA JADWAL SHOLAT
    ===================================================================== --}}
    <table class="table table-bordered mt-4">
        <thead class="table-light">
            <tr>
                <th>Tanggal</th>
                <th>Imsak</th>
                <th>Subuh</th>
                <th>Syuruq</th>
                <th>Dhuha</th>
                <th>Dzuhur</th>
                <th>Ashar</th>
                <th>Maghrib</th>
                <th>Isya</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($schedules as $s)
            <tr>
                <td>{{ $s->date }}</td>
                <td>{{ $s->imsak }}</td>
                <td>{{ $s->subuh }}</td>
                <td>{{ $s->syuruq }}</td>
                <td>{{ $s->dhuha }}</td>
                <td>{{ $s->dzuhur }}</td>
                <td>{{ $s->ashar }}</td>
                <td>{{ $s->maghrib }}</td>
                <td>{{ $s->isya }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted">
                    Belum ada data jadwal tampil.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection
