<?php

use App\Models\Schedule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public $schedules;

    public function mount()
    {
        // Hanya tampilkan jadwal yang akan datang (atau hari ini)
        $this->schedules = Schedule::where('schedule_date', '>=', now()->startOfDay())
            ->orderBy('schedule_date', 'asc')
            ->get();
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif">Kalender Posyandu</h2>
            <p class="text-dark mb-0">Lihat jadwal kegiatan rutin dan imunisasi yang akan datang.</p>
        </div>
    </div>

    @if($schedules->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-5 text-center">
                <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fa-regular fa-calendar-xmark fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark font-serif">Belum Ada Jadwal</h4>
                <p class="text-secondary mx-auto" style="max-width: 500px;">Kader Posyandu belum mempublikasikan jadwal kegiatan untuk bulan ini. Anda akan mendapatkan notifikasi WhatsApp jika jadwal baru telah dibuat.</p>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($schedules as $schedule)
                @php
                    $date = \Carbon\Carbon::parse($schedule->schedule_date);
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 rounded-4 h-100 shadow-sm hover-zoom overflow-hidden">
                        <!-- Header Banner -->
                        <div class="bg-primary-light p-3 border-bottom d-flex align-items-center">
                            <div class="bg-white text-center rounded-3 p-2 shadow-sm me-3" style="min-width: 60px;">
                                <div class="text-danger fw-bold small text-uppercase">{{ $date->translatedFormat('M') }}</div>
                                <div class="fs-4 fw-bolder text-dark lh-1">{{ $date->format('d') }}</div>
                            </div>
                            <div>
                                <h5 class="fw-bold text-primary-dark mb-0 font-serif">{{ $schedule->title }}</h5>
                                <span class="badge bg-white text-dark border mt-1"><i class="fa-regular fa-clock text-primary-dark me-1"></i> {{ $date->format('H:i') }} WIB</span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <div class="text-muted me-3"><i class="fa-solid fa-location-dot fs-5"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Lokasi Kegiatan</h6>
                                    <p class="text-muted small mb-0">{{ $schedule->location }}</p>
                                </div>
                            </div>
                            @if($schedule->description)
                            <div class="d-flex">
                                <div class="text-muted me-3"><i class="fa-regular fa-clipboard fs-5"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Catatan</h6>
                                    <p class="text-muted small mb-0">{{ $schedule->description }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-top-0 p-4 pt-0">
                            <button class="btn w-100 rounded-pill fw-bold text-white shadow-sm" style="background-color: #009639;">
                                <i class="fa-regular fa-calendar-check me-2"></i> Pengingat Aktif
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
