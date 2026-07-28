<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dasbor - SiPintar' }}</title>
    <!-- Fonts & Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Lora:wght@600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f3f4f2;
            border-right: 1px solid rgba(0,0,0,.05);
        }
        .nav-link.active {
            background-color: #009639; /* Hijau logo */
            color: #ffffff !important; /* Tulisan putih */
            font-weight: 700;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            margin-right: 15px;
        }
        .nav-link {
            color: #4b5563;
            border-radius: 0;
            padding: 12px 20px;
        }
        .nav-link:hover {
            background-color: #e5e7eb;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            margin-right: 15px;
        }
    </style>
</head>
<body style="background-color: #e8ecef; font-family: 'Inter', sans-serif;">

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar flex-shrink-0 d-none d-md-block shadow-sm" style="width: 250px;">
        <div class="d-flex align-items-center p-3 mb-3 border-bottom">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" height="28" class="me-2">
            <span class="fs-5 fw-bold text-dark" style="font-family: 'Lora', serif;">SiPintar</span>
        </div>
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" aria-current="page">
                    <i class="fa-solid fa-house me-2"></i> Dasbor
                </a>
            </li>
            @if(auth()->user()->role === 'warga')
            <li>
                <a href="{{ route('family.index') }}" class="nav-link {{ request()->routeIs('family*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users me-2"></i> Data Keluarga
                </a>
            </li>
            <li>
                <a href="{{ route('schedule.index') }}" class="nav-link {{ request()->routeIs('schedule.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-days me-2"></i> Kalender Posyandu
                </a>
            </li>
            @else
            <li>
                <a href="{{ route('targets.index') }}" class="nav-link {{ request()->routeIs('targets*') ? 'active' : '' }}">
                    <i class="fa-solid fa-people-group me-2"></i> Data Sasaran
                </a>
            </li>
            @if(auth()->user()->role === 'admin')
            <li>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-user me-2"></i> Data Warga
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('schedule.manage') }}" class="nav-link {{ request()->routeIs('schedule*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-plus me-2"></i> Kelola Jadwal
                </a>
            </li>
            <li>
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice me-2"></i> Laporan Posyandu
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- Main Content -->
    <div class="w-100 overflow-auto" style="height: 100vh;">
        <!-- Topbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-2 px-4">
            <div class="container-fluid">
                <button class="navbar-toggler border-0 shadow-none d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMobile">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 35px; height: 35px; background-color: #ffc107; color: #000000;">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="fw-medium d-none d-md-inline" style="font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="dropdownUser1">
                            <li class="px-3 py-2">
                                <form method="POST" action="{{ route('switch.role') }}">
                                    @csrf
                                    <label class="form-label small fw-bold mb-1 text-muted">Simulasi Peran:</label>
                                    <select name="role" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                                        <option value="warga" @if(auth()->user()->role == 'warga') selected @endif>Warga</option>
                                        <option value="kader" @if(auth()->user()->role == 'kader') selected @endif>Kader Posyandu</option>
                                        <option value="admin" @if(auth()->user()->role == 'admin') selected @endif>Admin/Bidan</option>
                                    </select>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger fw-bold" type="submit">
                                        <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Slot Content -->
        <main class="p-4">
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
