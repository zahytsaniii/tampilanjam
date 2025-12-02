@extends('layouts.admin')

@section('content')

<h1 class="text-2xl font-bold mb-6">
    {{ $schedule->exists ? 'Edit Jadwal' : 'Tambah Jadwal' }}
</h1>

<form action="{{ $schedule->exists
                ? route('schedule.update', $schedule)
                : route('schedule.store') }}"
      method="POST" class="bg-white p-6 shadow rounded">

    @csrf
    @if($schedule->exists)
        @method('PUT')
    @endif

    <div class="grid grid-cols-3 gap-4">

        <div>
            <label>Tanggal</label>
            <input type="date" name="date" class="form-input"
                   value="{{ old('date', $schedule->date) }}" required>
        </div>

        @foreach(['imsak','subuh','syuruq','dhuha','dzuhur','ashar','maghrib','isya'] as $field)
        <div>
            <label>{{ ucfirst($field) }}</label>
            <input type="time" name="{{ $field }}" class="form-input"
                   value="{{ old($field, $schedule->$field) }}">
        </div>
        @endforeach

    </div>

    <button class="mt-6 bg-blue-600 text-white px-4 py-2 rounded">
        Simpan
    </button>

</form>

@endsection
