# SiPinter (Sistem Informasi & Pemantauan Posyandu Terintegrasi)

Aplikasi berbasis web untuk manajemen dan pemantauan Posyandu yang terintegrasi, mencakup pendaftaran warga, pencatatan KMS (Kartu Menuju Sehat), manajemen jadwal, dan pelaporan otomatis.

## 🛠️ Stack Teknologi

Sistem ini dibangun dengan menggunakan arsitektur modern untuk memastikan performa yang cepat dan interaktif:

- **Backend Framework**: [Laravel 12](https://laravel.com)
- **Frontend Interactivity**: [Livewire Volt](https://livewire.laravel.com/docs/volt) (Single-file Livewire components)
- **Styling & UI Framework**: [Bootstrap 5](https://getbootstrap.com)
- **Asset Bundler**: Vite

## 📄 Dokumen Sistem

Untuk memahami arsitektur user (Warga, Kader, Admin) serta matriks hak akses untuk tiap modul, silakan merujuk pada:
- [Blueprint Sistem (blueprint.md)](./blueprint.md)

## 🔑 Kredensial Testing (Default Login)
Untuk mempermudah testing dan pengembangan, Anda bisa menjalankan `php artisan db:seed` untuk membuat 3 akun default dengan masing-masing level akses berikut. Password untuk semua akun adalah `password123`.

| Role | Username (NIK) | Password |
| :--- | :--- | :--- |
| **Admin** | `1111111111111111` | `password123` |
| **Kader** | `2222222222222222` | `password123` |
| **Warga** | `3333333333333333` | `password123` |
