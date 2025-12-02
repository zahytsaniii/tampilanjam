<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>

    <!-- Bootstrap CDN (tanpa npm) -->
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
        }
        .sidebar a:hover {
            background: #34495e;
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

    <a href="{{ route('dashboard') }}">Dashboard</a>
    <a href="{{ route('settings.index') }}">Pengaturan Masjid</a>
    <a href="{{ route('schedule.index') }}">Jadwal Sholat</a>
    <a href="{{ route('display.runningtext') }}">Running Text</a>
    <a href="{{ route('display.appearance') }}">Pengaturan Tampilan</a>

    <form class="mt-4 text-center" action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="btn btn-danger btn-sm">Logout</button>
    </form>
</div>

<div class="content">
    @yield('content')
</div>

</body>
</html>
