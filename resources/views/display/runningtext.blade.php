@extends('layouts.admin')

@section('content')

<div class="container">
    <h2>Manage Running Text</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    {{-- =====================================
        FORM TAMBAH / EDIT DALAM SATU HALAMAN
    ====================================== --}}
    <div class="card p-3 mb-4">

        @if(isset($editData))
            <h4>Edit Running Text</h4>
            <form action="{{ route('running-text.update', $editData->id) }}" method="POST">
                @csrf
                @method('PUT')
        @else
            <h4>Tambah Running Text</h4>
            <form action="{{ route('running-text.store') }}" method="POST">
                @csrf
        @endif

            <label>Pesan</label>
            <input type="text" name="message" class="form-control"
                   value="{{ $editData->message ?? '' }}" required>

            <label class="mt-2">Active</label>
            <select name="active" class="form-control">
                <option value="1" {{ isset($editData) && $editData->active ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ isset($editData) && !$editData->active ? 'selected' : '' }}>Nonaktif</option>
            </select>

            <button class="btn btn-success mt-3">
                {{ isset($editData) ? 'Update' : 'Tambah' }}
            </button>

            @if(isset($editData))
                <a href="{{ route('running-text.index') }}" class="btn btn-secondary mt-3">Batal</a>
            @endif

        </form>
    </div>


    {{-- =====================================
        TABEL DATA
    ====================================== --}}
    <table class="table table-bordered">
        <tr>
            <th>Pesan</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        @foreach ($texts as $t)
        <tr>
            <td>{{ $t->message }}</td>
            <td>{{ $t->active ? 'Aktif' : 'Nonaktif' }}</td>

            <td class="d-flex gap-2">

                {{-- BUTTON EDIT -> kembali ke halaman yang sama --}}
                <a href="{{ route('running-text.edit', $t->id) }}" class="btn btn-warning btn-sm">Edit</a>

                <form action="{{ route('running-text.destroy', $t->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm">Hapus</button>
                </form>

            </td>
        </tr>
        @endforeach
    </table>

</div>

@endsection
