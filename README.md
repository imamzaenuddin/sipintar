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

## 🚀 Panduan Instalasi Lokal (VSCode)

Ikuti langkah-langkah berikut untuk menjalankan aplikasi SiPintar di komputer Anda menggunakan Visual Studio Code:

1. **Clone Repository**
   ```bash
   git clone <url-repo-anda>
   cd sipintar
   ```

2. **Buka di VSCode**
   Buka folder proyek tersebut di VSCode. Anda juga bisa membukanya langsung via terminal dengan:
   ```bash
   code .
   ```

3. **Install Dependensi**
   Buka terminal terintegrasi di VSCode (tekan `` Ctrl + ` ``), lalu jalankan perintah berikut untuk menginstal dependensi PHP dan Node.js:
   ```bash
   composer install
   npm install
   ```

4. **Konfigurasi Environment (.env)**
   Salin file konfigurasi dasar:
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` yang baru dibuat di VSCode, lalu sesuaikan pengaturan database Anda. Pastikan database MySQL sudah dibuat sebelumnya (misalnya dengan nama `sipintar`):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sipintar
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi & Seeding**
   Langkah ini akan membuat struktur tabel database dan mengisi data *dummy* termasuk akun login default di atas:
   ```bash
   php artisan migrate --seed
   ```

7. **Jalankan Server Lokal**
   Anda membutuhkan dua terminal yang berjalan bersamaan.
   
   Di terminal pertama, jalankan Vite untuk melakukan *compile* aset *frontend* (Livewire/Bootstrap):
   ```bash
   npm run dev
   ```
   
   Buka terminal baru di VSCode (tekan tombol `+` di panel terminal), lalu jalankan server Laravel:
   ```bash
   php artisan serve
   ```
   
Aplikasi sekarang dapat diakses melalui browser di alamat: [http://localhost:8000](http://localhost:8000).
