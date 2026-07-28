<?php

use App\Models\KmsRecord;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Carbon;

new #[Layout('components.layouts.dashboard')] class extends Component {
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $filterMonth;
    public $filterYear;

    public function mount()
    {
        if (!in_array(auth()->user()->role, ['kader', 'admin'])) {
            abort(403, 'Akses khusus Kader / Admin.');
        }
        
        $this->filterMonth = now()->format('m');
        $this->filterYear = now()->format('Y');
    }

    public function with()
    {
        $records = KmsRecord::with(['familyMember.family.user'])
            ->whereMonth('recorded_date', $this->filterMonth)
            ->whereYear('recorded_date', $this->filterYear)
            ->orderBy('recorded_date', 'desc')
            ->paginate(15);
            
        return [
            'records' => $records,
            'totalPengukuran' => KmsRecord::whereMonth('recorded_date', $this->filterMonth)->whereYear('recorded_date', $this->filterYear)->count(),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif text-dark">Laporan Posyandu Bulanan</h2>
            <p class="text-muted mb-0">Rekapitulasi pengukuran dan penimbangan Balita.</p>
        </div>
        <div>
            <a href="{{ route('reports.print') }}" target="_blank" class="btn rounded-pill px-4 fw-bold shadow-sm" style="background-color: #009639; color: white;">
                <i class="fa-solid fa-print me-2"></i> Cetak Dokumen F1
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="border-top: 5px solid #ffc107 !important;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold text-dark font-serif mb-0"><i class="fa-solid fa-list-check text-warning me-2"></i> Rekap Penimbangan</h5>
                </div>
                <div class="col-md-6 mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
                    <select wire:model.live="filterMonth" class="form-select w-auto bg-light border-0 fw-bold">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                    <select wire:model.live="filterYear" class="form-select w-auto bg-light border-0 fw-bold">
                        @for($y=date('Y'); $y>=date('Y')-2; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            @if($records->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-regular fa-folder-open text-muted opacity-25 mb-3" style="font-size: 3rem;"></i>
                    <h6 class="text-dark fw-bold">Belum Ada Data Penimbangan</h6>
                    <p class="text-muted small">Tidak ada anak yang ditimbang pada bulan dan tahun ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-4">Tanggal</th>
                                <th>Nama Balita</th>
                                <th>Umur saat Ukur</th>
                                <th>BB (Kg)</th>
                                <th>TB (Cm)</th>
                                <th>Status Gizi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @foreach($records as $record)
                                <tr>
                                    <td class="ps-4 text-muted fw-bold">{{ \Carbon\Carbon::parse($record->recorded_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $record->familyMember->name }}</div>
                                        <div class="small text-muted">Ortu: {{ $record->familyMember->family->user->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $diff = \Carbon\Carbon::parse($record->familyMember->birth_date)->diff(\Carbon\Carbon::parse($record->recorded_date));
                                        @endphp
                                        <span class="badge bg-light text-dark border">{{ $diff->y }}th {{ $diff->m }}bln {{ $diff->d }}hr</span>
                                    </td>
                                    <td><span class="fw-bold text-primary-dark">{{ $record->weight }}</span></td>
                                    <td>{{ $record->height }}</td>
                                    <td>
                                        @if(str_contains($record->status_gizi, 'Baik'))
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">{{ $record->status_gizi }}</span>
                                        @elseif(str_contains($record->status_gizi, 'Kurang'))
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">{{ $record->status_gizi }}</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-25 text-dark rounded-pill px-3">{{ $record->status_gizi }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if($records->hasPages())
        <div class="card-footer bg-white border-top-0 pt-3 pb-4 px-4">
            {{ $records->links() }}
        </div>
        @endif
    </div>
</div>
