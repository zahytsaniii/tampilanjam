@extends('layouts.admin')

@section('content')
<div class="container">

    <h2>Manage Ayat Al-Qur'an</h2>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- =====================================
        FORM TAMBAH / EDIT
    ====================================== --}}
    <div class="card p-3 mb-4">

        @isset($editData)
            <h4>Edit Ayat</h4>
            <form action="{{ route('quran-verse.update', $editData->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h4>Tambah Ayat</h4>
            <form action="{{ route('quran-verse.store') }}" method="POST">
                @csrf
        @endisset

            <div class="mb-2">
                <label class="form-label fw-bold">Surah</label>
                <input type="text"
                       name="surah"
                       class="form-control"
                       placeholder="QS. Al-Baqarah: 38"
                       value="{{ $editData->surah ?? '' }}"
                       required>
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold">Ayat (Arab)</label>
                <textarea name="arabic_text"
                          class="form-control"
                          rows="2"
                          required>{{ $editData->arabic_text ?? '' }}</textarea>
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold">Terjemahan</label>
                <textarea name="translation"
                          class="form-control"
                          rows="2"
                          required>{{ $editData->translation ?? '' }}</textarea>
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold">Status</label>
                <select name="active" class="form-control">
                    <option value="1"
                        {{ isset($editData) && $editData->active ? 'selected' : '' }}>
                        Aktif
                    </option>
                    <option value="0"
                        {{ isset($editData) && isset($editData) && !$editData->active ? 'selected' : '' }}>
                        Nonaktif
                    </option>
                </select>
            </div>

            <button class="btn btn-success mt-3">
                {{ isset($editData) ? 'Update' : 'Tambah' }}
            </button>

            @isset($editData)
                <a href="{{ route('quran-verse.index') }}"
                   class="btn btn-secondary mt-3">
                    Batal
                </a>
            @endisset

        </form>
    </div>

    {{-- =====================================
        TABEL DATA
    ====================================== --}}
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Surah</th>
                <th>Ayat (Arab)</th>
                <th>Terjemahan</th>
                <th>Status</th>
                <th width="160">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($verses as $v)
            <tr>
                <td>{{ $v->surah }}</td>
                <td style="font-size:18px">{{ $v->arabic_text }}</td>
                <td>{{ $v->translation }}</td>
                <td>
                    {{ $v->active ? 'Aktif' : 'Nonaktif' }}
                </td>
                <td class="d-flex gap-2">

                    {{-- EDIT --}}
                    <a href="{{ route('quran-verse.index', ['edit' => $v->id]) }}"
                       class="btn btn-warning btn-sm">
                        Edit
                    </a>

                    {{-- DELETE --}}
                    <form action="{{ route('quran-verse.destroy', $v->id) }}"
                          method="POST"
                          onsubmit="return confirm('Hapus ayat ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            Hapus
                        </button>
                    </form>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
