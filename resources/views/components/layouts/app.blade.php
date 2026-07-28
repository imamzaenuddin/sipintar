<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SiPintar - Sistem Informasi & Pemantauan Posyandu Terintegrasi' }}</title>
    <!-- Fonts & Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light" style="font-family: 'Inter', sans-serif; min-height: 100vh; background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); display: flex; flex-direction: column;">
    {{ $slot }}
</body>
</html>
