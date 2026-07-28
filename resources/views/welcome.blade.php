<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPintar - Sistem Informasi & Pemantauan Posyandu Terintegrasi</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif; min-height: 100vh; background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); display: flex; flex-direction: column;">
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light glass sticky-top py-3 border-bottom border-white border-opacity-50">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bolder text-gradient fs-3" href="#">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Bekasi" width="40" height="45" class="me-2 drop-shadow">
                SiPintar
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a class="nav-link active fw-semibold" href="#">Beranda</a>
                    </li>
                    <li class="nav-item me-4">
                        <a class="nav-link fw-semibold text-secondary" href="#fitur">Fitur Unggulan</a>
                    </li>
                    <li class="nav-item mt-3 mt-lg-0 me-lg-2 w-100 w-lg-auto">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold w-100 w-lg-auto mb-2 mb-lg-0">Masuk</a>
                    </li>
                    <li class="nav-item mt-2 mt-lg-0 w-100 w-lg-auto">
                        <a href="{{ route('register') }}" class="btn btn-primary text-white rounded-pill px-4 fw-bold shadow-sm hover-zoom w-100 w-lg-auto">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="container py-5 mt-lg-5 flex-grow-1">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5 text-center text-lg-start">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-4 fw-bold shadow-sm border border-primary border-opacity-25">Platform Posyandu Digital #1</span>
                <h1 class="display-4 fw-bolder mb-4 text-dark" style="line-height: 1.25; letter-spacing: -1px;">
                    Pantau Tumbuh Kembang Anak Lebih <span class="text-gradient">Modern & Mudah</span>
                </h1>
                <p class="lead text-secondary mb-5 fw-medium" style="line-height: 1.7;">
                    SiPintar membawa pengalaman Posyandu ke dalam genggaman Anda. Dapatkan KMS Digital secara real-time dan notifikasi jadwal langsung di HP.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ route('register') }}" class="btn btn-primary text-white btn-lg rounded-pill px-5 shadow-lg hover-zoom fw-bold">Mulai Sekarang</a>
                    <a href="#fitur" class="btn btn-light btn-lg rounded-pill px-4 shadow-sm hover-zoom text-primary fw-bold bg-white border-0">Pelajari Fitur</a>
                </div>
            </div>
            <div class="col-lg-6 text-center position-relative">
                <!-- Decorative Blur Blobs behind the image -->
                <div class="position-absolute rounded-circle bg-primary opacity-25" style="width: 300px; height: 300px; top: 10%; right: 10%; filter: blur(60px); z-index: -1;"></div>
                <div class="position-absolute rounded-circle bg-info opacity-25" style="width: 250px; height: 250px; bottom: 5%; left: 15%; filter: blur(50px); z-index: -1;"></div>
                
                <img src="{{ asset('images/hero.png') }}" class="img-fluid rounded-4 shadow-lg hover-zoom" alt="Hero Posyandu" style="max-height: 550px; object-fit: cover; border: 8px solid rgba(255,255,255,0.5);">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-5 my-5 position-relative">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <h2 class="fw-bolder display-6 text-dark" style="letter-spacing: -0.5px;">Mengapa Memilih SiPintar?</h2>
                <p class="text-secondary lead fw-medium">Kemudahan akses data kesehatan dan penjadwalan dalam satu aplikasi terpusat.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <!-- Feature 1 -->
                <div class="col-md-6 col-lg-5">
                    <div class="card glass border-0 rounded-4 p-4 h-100 hover-zoom text-center shadow-sm">
                        <img src="{{ asset('images/feature_kms.png') }}" class="img-fluid rounded-4 mb-4 mx-auto shadow-sm" alt="KMS Digital" style="max-height: 250px; object-fit: cover; width: 100%;">
                        <h4 class="fw-bolder text-dark mb-3">KMS Digital <span class="text-primary">Real-time</span></h4>
                        <p class="text-secondary mb-0 fw-medium" style="line-height: 1.6;">Lupakan buku KMS yang mudah terselip. Pantau grafik BB, TB, dan indikator stunting si kecil dengan kalkulasi Z-Score standar WHO yang divisualisasikan secara indah.</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="col-md-6 col-lg-5">
                    <div class="card glass border-0 rounded-4 p-4 h-100 hover-zoom text-center shadow-sm">
                        <img src="{{ asset('images/feature_calendar.png') }}" class="img-fluid rounded-4 mb-4 mx-auto shadow-sm" alt="Jadwal & Notifikasi" style="max-height: 250px; object-fit: cover; width: 100%;">
                        <h4 class="fw-bolder text-dark mb-3">Notifikasi <span class="text-primary">WhatsApp</span></h4>
                        <p class="text-secondary mb-0 fw-medium" style="line-height: 1.6;">Tidak ada lagi kata lupa. Terima pengingat otomatis via WhatsApp untuk jadwal penimbangan dan imunisasi berikutnya di kelurahan Anda secara tepat waktu.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-white py-4 mt-auto border-top">
        <div class="container text-center text-muted">
            <p class="mb-0 fw-semibold fs-6">&copy; {{ date('Y') }} SiPintar - Sistem Informasi & Pemantauan Posyandu Terintegrasi.</p>
        </div>
    </footer>

</body>
</html>
