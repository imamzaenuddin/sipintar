<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public function with()
    {
        return [
            'user' => auth()->user(),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Halo, {{ $user->name }}! 👋</h2>
            <p class="text-muted mb-0">Pantau selalu kesehatan keluarga Anda melalui SiPintar.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 hover-zoom" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-users fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-primary mb-0 fw-bold">Anggota Keluarga</h6>
                        <h3 class="fw-bolder mb-0 text-dark">0</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 hover-zoom" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-calendar-check fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-success mb-0 fw-bold">Jadwal Mendatang</h6>
                        <h3 class="fw-bolder mb-0 text-dark">-</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 hover-zoom" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-bell fs-5"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-warning mb-0 fw-bold">Notifikasi</h6>
                        <h3 class="fw-bolder mb-0 text-dark">0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State for KMS -->
    <div class="card glass border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <img src="{{ asset('images/feature_kms.png') }}" alt="Ilustrasi KMS" height="180" class="mb-4 opacity-75 drop-shadow">
            <h4 class="fw-bold text-dark">Belum Ada Data KMS</h4>
            <p class="text-secondary mx-auto" style="max-width: 500px;">Anda belum mendaftarkan data keluarga atau KMS balita. Silakan lengkapi profil keluarga Anda agar dapat menggunakan fitur visualisasi Grafik Pertumbuhan WHO.</p>
            <button class="btn btn-primary rounded-pill px-5 fw-bold mt-3 shadow-sm hover-zoom">
                <i class="fa-solid fa-plus me-2"></i> Tambah Data Keluarga
            </button>
        </div>
    </div>
</div>
