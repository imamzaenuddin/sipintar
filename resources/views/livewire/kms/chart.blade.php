<?php

use App\Models\FamilyMember;
use App\Models\KmsRecord;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public $memberId;
    public $member;
    public $records;
    
    // Chart data arrays
    // Chart data arrays (Umum & Dewasa)
    public $chartLabels = [];
    public $chartDataWeight = [];
    public $chartDataHeight = [];
    
    // Chart data arrays (Balita)
    public $kmsTableData = [];
    public $chartDataWeightBalita = [];
    
    // ILP Data
    public $chartDataSys = [];
    public $chartDataDia = [];
    public $chartDataSugar = [];
    public $age_category = 'balita';

    public function mount($memberId)
    {
        $this->memberId = $memberId;
        $this->member = FamilyMember::findOrFail($memberId);
        
        // Hanya keluarga yang bersangkutan atau Kader/Admin yang boleh lihat
        if (auth()->user()->role === 'warga' && $this->member->family->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        $diff = \Carbon\Carbon::parse($this->member->birth_date)->diff(now());
        if ($diff->y < 6) {
            $this->age_category = 'balita';
        } else {
            $this->age_category = 'dewasa';
        }

        $this->fetchData();
    }

    public function fetchData()
    {
        $this->records = KmsRecord::where('family_member_id', $this->memberId)
            ->orderBy('recorded_date', 'asc')
            ->get();
            
        $birthDate = \Carbon\Carbon::parse($this->member->birth_date);
        
        // Inisialisasi struktur KMS (Bulan 0 - 60)
        if ($this->age_category === 'balita') {
            for ($i = 0; $i <= 60; $i++) {
                $this->kmsTableData[$i] = [
                    'month_str' => '',
                    'weight' => '',
                    'kbm' => '-',
                    'nt' => '',
                    'asi' => ''
                ];
                $this->chartDataWeightBalita[$i] = null;
            }
        }

        foreach ($this->records as $record) {
            $recordDate = \Carbon\Carbon::parse($record->recorded_date);
            
            if ($this->age_category === 'dewasa') {
                $this->chartLabels[] = $recordDate->translatedFormat('M Y');
                $this->chartDataWeight[] = $record->weight;
                $this->chartDataHeight[] = $record->height;
                
                if ($record->blood_pressure) {
                    $bp = explode('/', $record->blood_pressure);
                    $this->chartDataSys[] = count($bp) == 2 ? (int)$bp[0] : null;
                    $this->chartDataDia[] = count($bp) == 2 ? (int)$bp[1] : null;
                } else {
                    $this->chartDataSys[] = null;
                    $this->chartDataDia[] = null;
                }
                $this->chartDataSugar[] = $record->blood_sugar;
            } else {
                // Balita KMS Mapping
                $ageInMonths = (int) $birthDate->diffInMonths($recordDate);
                if ($ageInMonths >= 0 && $ageInMonths <= 60) {
                    $this->kmsTableData[$ageInMonths]['month_str'] = $recordDate->translatedFormat('M Y');
                    $this->kmsTableData[$ageInMonths]['weight'] = $record->weight;
                    $this->chartDataWeightBalita[$ageInMonths] = $record->weight;
                }
            }
        }
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif">KMS Digital</h2>
            <p class="text-dark mb-0">Pemantauan Tumbuh Kembang <strong class="text-primary-dark">{{ $member->name }}</strong>.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="javascript:history.back()" class="btn btn-light rounded-pill px-4 shadow-sm fw-bold border">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
            @if(in_array(auth()->user()->role, ['kader', 'admin']))
            <a href="{{ route('kms.form', $memberId) }}" class="btn rounded-pill px-4 fw-bold shadow-sm" style="background-color: #ffc107; color: #000;">
                <i class="fa-solid fa-plus me-1"></i> Input Data
            </a>
            @endif
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #009639;">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">Umur Saat Ini</p>
                    @php
                        $umurSaatIni = \Carbon\Carbon::parse($member->birth_date)->diff(now());
                    @endphp
                    <h4 class="fw-bolder text-dark mb-0 font-serif">{{ $umurSaatIni->y }}<span class="fs-6 fw-normal text-muted me-1">th</span> {{ $umurSaatIni->m }}<span class="fs-6 fw-normal text-muted me-1">bln</span> {{ $umurSaatIni->d }}<span class="fs-6 fw-normal text-muted">hr</span></h4>
                </div>
            </div>
        </div>
        
        @php
            $latest = $records->last();
        @endphp

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #0d6efd;">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">Berat Terakhir</p>
                    <h3 class="fw-bolder text-dark mb-0 font-serif">{{ $latest ? $latest->weight : '-' }} <span class="fs-6 fw-normal text-muted">Kg</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #8b5cf6;">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">Tinggi Terakhir</p>
                    <h3 class="fw-bolder text-dark mb-0 font-serif">{{ $latest ? $latest->height : '-' }} <span class="fs-6 fw-normal text-muted">Cm</span></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid {{ $latest && str_contains($latest->status_gizi, 'Baik') ? '#009639' : '#dc3545' }};">
                <div class="card-body">
                    <p class="text-muted small fw-bold mb-1">Status Gizi (Terakhir)</p>
                    <h5 class="fw-bolder mb-0 mt-2 {{ $latest && str_contains($latest->status_gizi, 'Baik') ? 'text-primary-dark' : 'text-danger' }}">
                        {{ $latest ? $latest->status_gizi : 'Belum ada data' }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik KMS -->
    @if($age_category === 'balita')
        @php
            $isFemale = strtolower($member->gender ?? 'perempuan') == 'perempuan';
            $themeColor = $isFemale ? '#e83e8c' : '#0d6efd'; // Pink or Blue
            $bgTheme = $isFemale ? '#ffe4ec' : '#e0f3ff';
        @endphp
        <div class="card border-0 shadow-sm mb-4" style="border-top: 5px solid {{ $themeColor }} !important; border-radius: 0;">
            <div class="card-header border-bottom-0 py-3" style="background-color: {{ $bgTheme }}; border-radius: 0;">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 rounded-circle shadow-sm me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="fa-solid fa-children fs-1" style="color: {{ $themeColor }};"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold mb-0 lh-1" style="color: {{ $themeColor }}; font-family: 'Arial', sans-serif; font-size: 2.5rem;">KMS</h2>
                                <div class="text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 14px; color: {{ $themeColor }};">KARTU MENUJU SEHAT</div>
                                <div class="fw-bold bg-white px-2 mt-1 text-uppercase" style="display:inline-block; font-size:12px; color: {{ $themeColor }};">
                                    Untuk {{ $isFemale ? 'Perempuan' : 'Laki-laki' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="mb-2 border-bottom pb-1 d-flex" style="border-color: rgba(0,0,0,0.1) !important;">
                            <span class="fw-bold me-3 text-danger">Nama Anak</span>
                            <span class="flex-grow-1" style="color: {{ $themeColor }};">: <strong class="text-dark">{{ $member->name }}</strong></span>
                        </div>
                        <div class="border-bottom pb-1 d-flex" style="border-color: rgba(0,0,0,0.1) !important;">
                            <span class="fw-bold me-3 text-danger">Nama Posyandu</span>
                            <span class="flex-grow-1" style="color: {{ $themeColor }};">: <strong class="text-dark">Posyandu SiPintar</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center py-3">
                <h4 class="fw-bold text-dark mb-1 font-serif">Timbanglah Anak Anda Setiap Bulan</h4>
                <h5 class="fw-bold text-dark font-serif">Anak Sehat, Tambah Umur Tambah Berat, Tambah Pandai</h5>
            </div>

            <div class="card-body p-0" style="overflow-x: auto;">
                <div style="min-width: 1000px; padding: 10px 20px 20px 20px;">
                    <!-- Chart Canvas -->
                    <div style="height: 450px; position: relative;">
                        <canvas id="kmsChart"></canvas>
                    </div>
                    <!-- Table dihapus sesuai permintaan -->
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 rounded-4 shadow-sm mb-4" style="border-top: 5px solid #009639 !important;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-area text-primary-dark me-2"></i> Grafik Tren Kesehatan (ILP)</h5>
            </div>
            <div class="card-body p-4">
                @if($records->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa-solid fa-chart-line text-muted opacity-25 mb-3" style="font-size: 4rem;"></i>
                        <h5 class="text-dark fw-bold">Belum Ada Data Penimbangan</h5>
                    </div>
                @else
                    <div style="height: 350px; width: 100%;">
                        <canvas id="kmsChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Histori Tabel -->
    <div class="card border-0 rounded-4 shadow-sm" style="border-top: 5px solid #ffc107 !important;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list text-warning me-2"></i> Riwayat Pengukuran</h5>
        </div>
        <div class="card-body p-4">
            @if($records->isEmpty())
                <p class="text-muted text-center my-3">Belum ada riwayat.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>Tanggal</th>
                                <th>Hasil Pengukuran Utama</th>
                                <th>Status Gizi</th>
                                <th>Catatan Pemeriksaan</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($records->reverse() as $record)
                                <tr>
                                    <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($record->recorded_date)->translatedFormat('d M Y') }}</td>
                                    <td>
                                        @if($age_category === 'balita')
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill mb-1">BB: {{ $record->weight }} kg</span>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill mb-1">TB: {{ $record->height }} cm</span>
                                            <div class="small text-muted mt-1">LK: {{ $record->head_circumference ?? '-' }}cm | LILA: {{ $record->lila ?? '-' }}cm</div>
                                        @else
                                            <div class="mb-1">
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill">Tensi: {{ $record->blood_pressure ?? '-' }}</span>
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill">Gula: {{ $record->blood_sugar ?? '-' }}</span>
                                            </div>
                                            <div class="small text-muted">BB: {{ $record->weight }}kg | LP: {{ $record->belly_circumference ?? '-' }}cm | Asam Urat: {{ $record->uric_acid ?? '-' }} | Kol: {{ $record->cholesterol ?? '-' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(str_contains($record->status_gizi, 'Baik') || str_contains($record->status_gizi, 'Normal'))
                                            <span class="text-success fw-bold"><i class="fa-solid fa-circle-check me-1"></i> {{ $record->status_gizi }}</span>
                                        @else
                                            <span class="text-warning fw-bold"><i class="fa-solid fa-circle-exclamation me-1"></i> {{ $record->status_gizi }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->examination_notes)
                                            <div class="small text-dark" style="max-width: 300px;">{!! nl2br(e($record->examination_notes)) !!}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $record->recorder->name ?? '-' }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@if($records->isNotEmpty())
    @script
    <script>
        // Mengambil data dari variabel PHP/Livewire ke JS
        const ctx = document.getElementById('kmsChart');
        if (ctx) {
            const labels = @json($chartLabels);
            const weightData = @json($chartDataWeight);
            const heightData = @json($chartDataHeight);
            const ageCategory = '{{ $age_category }}';
            
            let datasets = [];
            
            if (ageCategory === 'balita') {
                const labelsBalita = Array.from({length: 61}, (_, i) => i);
                const rawWeightData = @json($chartDataWeightBalita);
                let weightDataBalita = [];
                for (let i = 0; i <= 60; i++) {
                    weightDataBalita.push(rawWeightData[i] ? parseFloat(rawWeightData[i]) : null);
                }
                
                // Mock WHO curves (0-60)
                datasets.push({
                    label: 'Garis Merah Atas',
                    data: labelsBalita.map(i => 5 + (i * 0.28)),
                    borderColor: 'red',
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: false, // Index 0
                    order: 10
                });
                datasets.push({
                    label: 'Garis Kuning Atas',
                    data: labelsBalita.map(i => 4 + (i * 0.26)),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.6)', // Kuning
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: 0, // Fill to index 0
                    order: 10
                });
                datasets.push({
                    label: 'Garis Hijau Atas',
                    data: labelsBalita.map(i => 3.2 + (i * 0.24)),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.6)', // Hijau
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: 1, // Fill to index 1
                    order: 10
                });
                datasets.push({
                    label: 'Garis Hijau Bawah',
                    data: labelsBalita.map(i => 2.5 + (i * 0.21)),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.6)', // Hijau
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: 2, // Fill to index 2
                    order: 10
                });
                datasets.push({
                    label: 'Garis Merah Bawah',
                    data: labelsBalita.map(i => 2 + (i * 0.18)),
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 193, 7, 0.6)', // Kuning
                    borderWidth: 1,
                    pointRadius: 0,
                    fill: 3, // Fill to index 3
                    order: 10
                });
                
                // Actual Weight Data Anak
                datasets.push({
                    label: 'Berat Badan Anak (kg)',
                    data: weightDataBalita,
                    borderColor: '#000',
                    backgroundColor: '#000',
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#000',
                    pointBorderWidth: 3,
                    pointRadius: 7,
                    pointHoverRadius: 9,
                    fill: false,
                    tension: 0, // Garis lurus antar titik (aturan baku KMS)
                    spanGaps: true, // Tarik garis lurus meskipun ada bulan yang bolong (tidak nimbang)
                    order: 1 // Gambar paling atas (di atas pita warna)
                });

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labelsBalita,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: { bottom: 0 }
                        },
                        plugins: {
                            legend: { display: false } // Hide legend to match physical layout
                        },
                        scales: {
                            y: { 
                                min: 0,
                                max: 25,
                                ticks: { stepSize: 1 },
                                grid: { color: 'rgba(0,0,0,0.2)' }
                            },
                            x: { 
                                grid: { color: 'rgba(0,0,0,0.2)' }
                            }
                        }
                    }
                });
            } else {
                // ILP Adult Chart (BP and Sugar)
                const sysData = @json($chartDataSys);
                const diaData = @json($chartDataDia);
                const sugarData = @json($chartDataSugar);
                
                datasets = [
                    {
                        label: 'Tensi Sistolik',
                        data: sysData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 3,
                        pointRadius: 5,
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Tensi Diastolik',
                        data: diaData,
                        borderColor: '#fd7e14',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        pointRadius: 5,
                        fill: false,
                        tension: 0.3
                    },
                    {
                        label: 'Gula Darah',
                        data: sugarData,
                        borderColor: '#0d6efd',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        pointRadius: 5,
                        fill: false,
                        tension: 0.3
                    }
                ];

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { font: { family: "'Inter', sans-serif", weight: 'bold' } } }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        }
    </script>
    @endscript
@endif
