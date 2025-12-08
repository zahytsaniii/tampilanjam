@extends('layouts.admin')

@section('content')

<div class="container">

    <h2 class="mb-4">Pengaturan Tampilan</h2>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    {{-- =====================================
        FORM PEMILIHAN TEMPLATE
    ====================================== --}}
    <div class="card p-3 mb-4 shadow-sm">

        <h4 class="mb-3">Pilih Template Tampilan</h4>

        <form method="POST" action="{{ route('display.appearance') }}">
            @csrf

            <div class="row">

                {{-- TEMPLATE 1 --}}
                <div class="col-md-6 mb-3">
                    <label class="w-100">
                        <div class="card p-2 text-center border 
                            {{ ($settings['theme'] ?? 'theme1') == 'theme1' ? 'border-primary' : '' }}">
                            
                            <img src="{{ asset('images/preview-theme1.jpg') }}"
                                 class="img-fluid rounded mb-2"
                                 style="height:200px;object-fit:cover">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="theme"
                                       value="theme1"
                                       {{ ($settings['theme'] ?? 'theme1') == 'theme1' ? 'checked' : '' }}>

                                <span class="ms-2 fw-bold">Template 1</span>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- TEMPLATE 2 --}}
                <div class="col-md-6 mb-3">
                    <label class="w-100">
                        <div class="card p-2 text-center border 
                            {{ ($settings['theme'] ?? 'theme1') == 'theme2' ? 'border-primary' : '' }}">
                            
                            <img src="{{ asset('images/preview-theme2.jpg') }}"
                                 class="img-fluid rounded mb-2"
                                 style="height:200px;object-fit:cover">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="theme"
                                       value="theme2"
                                       {{ ($settings['theme'] ?? 'theme1') == 'theme2' ? 'checked' : '' }}>

                                <span class="ms-2 fw-bold">Template 2</span>
                            </div>
                        </div>
                    </label>
                </div>

            </div>

            <button class="btn btn-success mt-3">
                Simpan Pengaturan
            </button>

        </form>
    </div>


    {{-- =====================================
        INFO TEMPLATE AKTIF
    ====================================== --}}
    <div class="card p-3 shadow-sm">

        <h4>Template Aktif Saat Ini</h4>

        <table class="table table-bordered mt-2">
            <tr>
                <th width="200">Template Aktif</th>
                <td>
                    <span class="badge bg-primary">
                        {{ strtoupper($settings['theme'] ?? 'THEME1') }}
                    </span>
                </td>
            </tr>

            <tr>
                <th>Keterangan</th>
                <td>
                    {{
                        ($settings['theme'] ?? 'theme1') == 'theme1'
                        ? 'Template dengan tampilan sidebar biru & background masjid'
                        : 'Template dengan layout horizontal modern'
                    }}
                </td>
            </tr>
        </table>

    </div>


</div>

@endsection
