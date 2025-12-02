@extends('layouts.admin')

@section('content')
<h2>Dashboard Admin</h2>
<p>Selamat datang, {{ auth()->user()->name }}!</p>

<div class="row mt-4">

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Pengaturan Masjid</h5>
            <p>Atur nama masjid, alamat, lat/long, dsb.</p>
            <a href="{{ route('settings.index') }}" class="btn btn-primary btn-sm">Atur</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Jadwal Sholat</h5>
            <p>Kelola dan lihat jadwal sholat.</p>
            <a href="{{ route('schedule.index') }}" class="btn btn-primary btn-sm">Lihat</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Running Text</h5>
            <p>Atur informasi running text yang tampil di layar.</p>
            <a href="{{ route('display.runningtext') }}" class="btn btn-primary btn-sm">Atur</a>
        </div>
    </div>

</div>
@endsection
