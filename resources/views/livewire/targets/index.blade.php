<?php

use App\Models\FamilyMember;
use App\Models\Schedule;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

new #[Layout('components.layouts.dashboard')] class extends Component {
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $filterAge = 'all'; // all, balita, remaja, produktif, lansia
    public $filterSchedule = ''; // id schedule
    public $schedules = [];
    public $selectedScheduleDate = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterAge()
    {
        $this->resetPage();
    }
    
    public function updatedFilterSchedule()
    {
        if ($this->filterSchedule) {
            $schedule = Schedule::find($this->filterSchedule);
            $this->selectedScheduleDate = $schedule ? $schedule->schedule_date : null;
        } else {
            $this->selectedScheduleDate = null;
        }
        $this->resetPage();
    }

    public function mount()
    {
        if (!in_array(auth()->user()->role, ['kader', 'admin'])) {
            abort(403, 'Akses khusus Kader / Admin.');
        }
        // Ambil jadwal 1 bulan ke belakang dan 1 bulan ke depan
        $this->schedules = Schedule::where('schedule_date', '>=', now()->subMonths(2))
                                   ->orderBy('schedule_date', 'desc')
                                   ->get();
    }

    public function with()
    {
        $query = FamilyMember::with(['family.user', 'kmsRecords']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterSchedule) {
            $schedule = Schedule::find($this->filterSchedule);
            if ($schedule && is_array($schedule->target_groups) && count($schedule->target_groups) > 0) {
                $query->where(function($q) {
                    $schedule = Schedule::find($this->filterSchedule);
                    $now = Carbon::now();
                    foreach ($schedule->target_groups as $tg) {
                        if ($tg === 'balita') {
                            $q->orWhere('birth_date', '>=', $now->copy()->subYears(6));
                        } elseif ($tg === 'remaja') {
                            $q->orWhere(function($sq) use ($now) {
                                $sq->where('birth_date', '<=', $now->copy()->subYears(10))
                                   ->where('birth_date', '>=', $now->copy()->subYears(19));
                            });
                        } elseif ($tg === 'produktif') {
                            $q->orWhere(function($sq) use ($now) {
                                $sq->where('birth_date', '<', $now->copy()->subYears(19))
                                   ->where('birth_date', '>=', $now->copy()->subYears(60));
                            });
                        } elseif ($tg === 'lansia') {
                            $q->orWhere('birth_date', '<', $now->copy()->subYears(60));
                        }
                    }
                });
            }
        } else {
            $now = Carbon::now();
            if ($this->filterAge === 'balita') {
                $query->where('birth_date', '>=', $now->copy()->subYears(6));
            } elseif ($this->filterAge === 'remaja') {
                $query->where('birth_date', '<=', $now->copy()->subYears(10))
                      ->where('birth_date', '>=', $now->copy()->subYears(19));
            } elseif ($this->filterAge === 'produktif') {
                $query->where('birth_date', '<', $now->copy()->subYears(19))
                      ->where('birth_date', '>=', $now->copy()->subYears(60));
            } elseif ($this->filterAge === 'lansia') {
                $query->where('birth_date', '<', $now->copy()->subYears(60));
            }
        }

        return [
            'members' => $query->orderBy('name', 'asc')->paginate(15),
        ];
    }
}; ?>

<div>
    @if (session()->has('error'))
        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 mb-4 d-flex align-items-center">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
            <div>
                <strong>Akses Ditolak!</strong> {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif text-dark">Data Sasaran Posyandu</h2>
            <p class="text-muted mb-0">Cari dan kelola sasaran posyandu berdasarkan kategori usia.</p>
        </div>
    </div>

    <!-- Statistik Ringkas -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #0d6efd;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-child-reaching text-primary fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0">Balita</h6>
                    </div>
                    <p class="text-muted small mb-0">0 - 5 Tahun</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #ffc107;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-person-skating text-warning fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0">Remaja</h6>
                    </div>
                    <p class="text-muted small mb-0">10 - 18 Tahun</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #009639;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-user-tie text-success fs-4 me-2"></i>
                        <h6 class="fw-bold mb-0">Usia Produktif</h6>
                    </div>
                    <p class="text-muted small mb-0">19 - 59 Tahun</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-left: 4px solid #8b5cf6;">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-person-cane" style="color:#8b5cf6; font-size:1.5rem; margin-right:0.5rem;"></i>
                        <h6 class="fw-bold mb-0">Lansia</h6>
                    </div>
                    <p class="text-muted small mb-0">≥ 60 Tahun</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="border-top: 5px solid #009639 !important;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3">
            
            @if($filterSchedule)
                @php
                    $sched = \App\Models\Schedule::find($filterSchedule);
                @endphp
                <div class="alert alert-info border-0 bg-info bg-opacity-10 text-dark rounded-3 mb-4 d-flex align-items-center">
                    <i class="fa-solid fa-layer-group fs-4 text-info me-3"></i>
                    <div>
                        <strong>Mode Pelayanan Terpadu (ILP) Aktif</strong><br>
                        Sistem memfilter otomatis untuk sasaran: 
                        @if($sched && is_array($sched->target_groups))
                            @foreach($sched->target_groups as $tg)
                                <span class="badge bg-info text-dark rounded-pill border">{{ ucfirst($tg) }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            @else
                <!-- Tabs Filter Umur -->
                <ul class="nav nav-pills mb-4 flex-nowrap overflow-auto hide-scrollbar" style="white-space: nowrap;">
                    <li class="nav-item me-2">
                        <button wire:click="$set('filterAge', 'all')" class="nav-link rounded-pill px-4 fw-bold shadow-sm {{ $filterAge === 'all' ? 'active' : 'bg-light text-muted' }}" style="{{ $filterAge === 'all' ? 'background-color: #009639;' : '' }}">Semua Umur</button>
                    </li>
                    <li class="nav-item me-2">
                        <button wire:click="$set('filterAge', 'balita')" class="nav-link rounded-pill px-4 fw-bold shadow-sm {{ $filterAge === 'balita' ? 'active bg-primary' : 'bg-light text-muted' }}">Balita</button>
                    </li>
                    <li class="nav-item me-2">
                        <button wire:click="$set('filterAge', 'remaja')" class="nav-link rounded-pill px-4 fw-bold shadow-sm {{ $filterAge === 'remaja' ? 'active bg-warning text-dark' : 'bg-light text-muted' }}">Remaja</button>
                    </li>
                    <li class="nav-item me-2">
                        <button wire:click="$set('filterAge', 'produktif')" class="nav-link rounded-pill px-4 fw-bold shadow-sm {{ $filterAge === 'produktif' ? 'active bg-success' : 'bg-light text-muted' }}">Usia Produktif</button>
                    </li>
                    <li class="nav-item">
                        <button wire:click="$set('filterAge', 'lansia')" class="nav-link rounded-pill px-4 fw-bold shadow-sm {{ $filterAge === 'lansia' ? 'active' : 'bg-light text-muted' }}" style="{{ $filterAge === 'lansia' ? 'background-color: #8b5cf6;' : '' }}">Lansia</button>
                    </li>
                </ul>
            @endif

            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="fw-bold text-dark font-serif mb-0">Daftar Sasaran Terdaftar</h5>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <select wire:model.live="filterSchedule" class="form-select bg-light fw-bold" style="border-color: #009639;">
                        <option value="">-- Wajib Pilih Agenda Pelaksanaan --</option>
                        @foreach($schedules as $s)
                            <option value="{{ $s->id }}">{{ $s->title }} ({{ \Carbon\Carbon::parse($s->schedule_date)->format('d M Y') }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-start-0" placeholder="Cari Nama atau NIK...">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama Lengkap</th>
                            <th>Umur (Thn/Bln)</th>
                            <th>Kepala Keluarga</th>
                            @if($filterSchedule)
                            <th class="text-center">Status Kehadiran</th>
                            @endif
                            <th class="text-end pe-4">Aksi KMS</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($members as $index => $m)
                            <tr>
                                <td class="ps-4 text-muted">{{ $members->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">
                                        {{ $m->name }}
                                        @if($m->gender === 'L')
                                            <i class="fa-solid fa-mars text-info ms-1" title="Laki-laki"></i>
                                        @else
                                            <i class="fa-solid fa-venus text-danger ms-1" title="Perempuan"></i>
                                        @endif
                                    </div>
                                    <span class="text-muted small font-monospace"><i class="fa-regular fa-id-card me-1"></i> {{ $m->nik ?? '-' }}</span>
                                </td>
                                <td>
                                    @php
                                        $diff = \Carbon\Carbon::parse($m->birth_date)->diff(now());
                                    @endphp
                                    <div class="fw-bold text-primary-dark">{{ $diff->y }}th {{ $diff->m }}bln {{ $diff->d }}hr</div>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $m->family->head_of_family_name ?? 'Tidak diketahui' }}</div>
                                    <div class="small text-muted">{{ $m->family->user->name ?? '-' }} (Akun Induk)</div>
                                </td>
                                @if($filterSchedule)
                                <td class="text-center">
                                    @php
                                        // Cari apakah anak ini sudah ditimbang pada tanggal agenda
                                        $targetDate = \Carbon\Carbon::parse($selectedScheduleDate)->format('Y-m-d');
                                        $isMeasured = $m->kmsRecords->contains(function($record) use ($targetDate) {
                                            return \Carbon\Carbon::parse($record->recorded_date)->format('Y-m-d') === $targetDate;
                                        });
                                    @endphp
                                    @if($isMeasured)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-2"><i class="fa-solid fa-check me-1"></i> Sudah Diukur</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-2"><i class="fa-solid fa-xmark me-1"></i> Belum Diukur</span>
                                    @endif
                                </td>
                                @endif
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('kms.chart', $m->id) }}" class="btn btn-sm btn-light border text-primary-dark shadow-sm" title="Lihat Grafik KMS">
                                            <i class="fa-solid fa-chart-line"></i> Grafik
                                        </a>
                                        @if($filterSchedule)
                                        <a href="{{ route('kms.form', ['memberId' => $m->id, 'schedule_id' => $filterSchedule]) }}" class="btn btn-sm shadow-sm fw-bold" style="background-color: #ffc107; color: #000;" title="Input Hasil Penimbangan">
                                            <i class="fa-solid fa-plus me-1"></i> Input
                                        </a>
                                        @else
                                        <button class="btn btn-sm btn-secondary shadow-sm fw-bold opacity-50" title="Pilih Agenda Pelaksanaan terlebih dahulu di atas" disabled>
                                            <i class="fa-solid fa-lock me-1"></i> Input
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fa-solid fa-magnifying-glass text-muted opacity-25 mb-3" style="font-size: 3rem;"></i>
                                    <h6 class="text-dark fw-bold">Tidak ada sasaran ditemukan</h6>
                                    <p class="text-muted small">Coba sesuaikan kata kunci pencarian atau filter umur Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($members->hasPages())
        <div class="card-footer bg-white border-top-0 pt-3 pb-4 px-4">
            {{ $members->links() }}
        </div>
        @endif
    </div>

    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</div>
