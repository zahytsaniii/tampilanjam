<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f2f4f6; }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            position: fixed;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #34495e;
        }

        /* ✅ MENU AKTIF */
        .sidebar a.active {
            background: #1abc9c;
            font-weight: bold;
            color: #fff;
        }

        /* ✅ SUBMENU */
        .sidebar .collapse a {
            font-size: 14px;
            background: #34495e;
        }

        .sidebar .collapse a:hover {
            background: #2c3e50;
        }

        .content {
            margin-left: 240px;
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center mb-4">Admin Panel</h4>

    <!-- ================= MENU UTAMA ================= -->

    <a href="{{ route('dashboard') }}"
       class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('schedule.index') }}"
       class="{{ request()->routeIs('schedule.*') ? 'active' : '' }}">
        Jadwal Sholat
    </a>

    <a href="{{ route('display.runningtext') }}"
       class="{{ request()->routeIs('display.runningtext') ? 'active' : '' }}">
        Running Text
    </a>

    <a href="{{ route('quran-verse.index') }}"
        class="{{ request()->routeIs('quran-verse.*') ? 'active' : '' }}">
         Ayat Al-Qur'an
    </a>

    <!-- ================= PENGATURAN (SMART COLLAPSE) ================= -->

    @php
        $pengaturanActive = request()->routeIs(
            'settings.*',
            'display.appearance',
            'device.settings'
        );
    @endphp

    <a class="d-flex justify-content-between align-items-center
              {{ $pengaturanActive ? 'active' : '' }}"
       data-bs-toggle="collapse"
       href="#menuPengaturan"
       role="button"
       aria-expanded="{{ $pengaturanActive ? 'true' : 'false' }}">

        <span>Pengaturan</span>
        <span class="small">▾</span>
    </a>

    <div class="collapse {{ $pengaturanActive ? 'show' : '' }}" id="menuPengaturan">
        <a class="ps-4 {{ request()->routeIs('settings.*') ? 'active' : '' }}"
           href="{{ route('settings.index') }}">
            • Masjid
        </a>

        <a class="ps-4 {{ request()->routeIs('display.appearance') ? 'active' : '' }}"
           href="{{ route('display.appearance') }}">
            • Tampilan
        </a>

        <a class="ps-4 {{ request()->routeIs('device.settings') ? 'active' : '' }}"
           href="{{ route('device.settings') }}">
            • Perangkat
        </a>
    </div>

    <!-- ================= LOGOUT ================= -->

    <form class="mt-4 text-center" action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="btn btn-danger btn-sm">Logout</button>
    </form>
</div>

<div class="content">
    @yield('content')
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
