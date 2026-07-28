<?php

use App\Models\User;
use App\Models\FamilyMember;
use App\Models\Schedule;
use App\Models\KmsRecord;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public function with()
    {
        // Total Warga
        $totalWarga = User::where('role', 'warga')->count();

        // Total Balita
        $totalBalita = FamilyMember::count();

        // Kasus Stunting Sederhana (Balita dengan KMS terakhir 'Gizi Kurang')
        $stuntingCount = 0;
        // Hanya contoh simulasi hitungan sederhana
        $stuntingCount = KmsRecord::where('status_gizi', 'Gizi Kurang')->distinct('family_member_id')->count();

        // Jadwal Aktif
        $jadwalAktif = Schedule::where('schedule_date', '>=', now()->startOfDay())->count();

        return [
            'user' => auth()->user(),
            'totalWarga' => $totalWarga,
            'totalBalita' => $totalBalita,
            'stuntingCount' => $stuntingCount,
            'jadwalAktif' => $jadwalAktif,
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif text-dark">Dasbor Kader / Admin</h2>
            <p class="text-muted mb-0">Selamat datang kembali, <strong class="text-primary-dark">{{ $user->name }}</strong>. Berikut ringkasan data Posyandu Anda.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-white text-dark shadow-sm px-3 py-2 border font-monospace">
                <i class="fa-regular fa-calendar text-primary-dark me-1"></i> {{ now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Total Warga -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-zoom" style="border-bottom: 4px solid #009639 !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-primary-light rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                </div>
                <h6 class="text-muted fw-bold mb-1">Total Warga (KK)</h6>
                <h2 class="fw-bolder text-dark mb-0 font-serif">{{ $totalWarga }} <span class="fs-6 fw-normal text-muted">Keluarga</span></h2>
            </div>
        </div>

        <!-- Total Balita -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-zoom" style="border-bottom: 4px solid #ffc107 !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-warning bg-opacity-25 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-child-reaching fs-4 text-dark"></i>
                    </div>
                </div>
                <h6 class="text-muted fw-bold mb-1">Total Balita</h6>
                <h2 class="fw-bolder text-dark mb-0 font-serif">{{ $totalBalita }} <span class="fs-6 fw-normal text-muted">Anak</span></h2>
            </div>
        </div>

        <!-- Kasus Stunting -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-zoom" style="border-bottom: 4px solid #dc3545 !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                    </div>
                </div>
                <h6 class="text-muted fw-bold mb-1">Indikasi Stunting</h6>
                <h2 class="fw-bolder text-danger mb-0 font-serif">{{ $stuntingCount }} <span class="fs-6 fw-normal text-muted">Kasus</span></h2>
            </div>
        </div>

        <!-- Jadwal Aktif -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-zoom" style="border-bottom: 4px solid #0d6efd !important;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-calendar-days fs-4"></i>
                    </div>
                </div>
                <h6 class="text-muted fw-bold mb-1">Jadwal Mendatang</h6>
                <h2 class="fw-bolder text-dark mb-0 font-serif">{{ $jadwalAktif }} <span class="fs-6 fw-normal text-muted">Kegiatan</span></h2>
            </div>
        </div>
    </div>

    <!-- Section Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-top: 5px solid #009639 !important;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-dark font-serif"><i class="fa-solid fa-bolt text-warning me-2"></i> Akses Cepat Kader</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="{{ route('schedule.manage') }}" class="btn btn-light border rounded-3 p-3 text-start hover-zoom">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    <i class="fa-solid fa-calendar-plus fs-5 text-primary-dark"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Buat Jadwal Posyandu</h6>
                                    <span class="text-muted small">Tambahkan jadwal kegiatan rutin bulan ini.</span>
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('reports.print') }}" target="_blank" class="btn btn-light border rounded-3 p-3 text-start hover-zoom">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                    <i class="fa-solid fa-print fs-5 text-dark"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Cetak Rekap KMS</h6>
                                    <span class="text-muted small">Cetak atau simpan data penimbangan sebagai dokumen PDF.</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Warga Baru (Simulasi) -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="border-top: 5px solid #ffc107 !important;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-dark font-serif"><i class="fa-solid fa-bell text-danger me-2"></i> Warga Baru Mendaftar</h5>
                </div>
                <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                    <i class="fa-solid fa-users-viewfinder text-muted opacity-25 mb-3" style="font-size: 4rem;"></i>
                    <h6 class="fw-bold text-dark">Modul Warga Akan Datang</h6>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 300px;">Fitur validasi dan daftar Warga yang baru registrasi mandiri akan segera diimplementasikan pada Modul 0.</p>
                </div>
            </div>
        </div>
    </div>
</div>
