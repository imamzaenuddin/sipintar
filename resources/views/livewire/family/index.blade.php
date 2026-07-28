<?php

use App\Models\Family;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public function with()
    {
        $user = Auth::user();
        $family = Family::where('user_id', $user->id)->with(['members', 'province', 'city', 'district', 'village'])->first();
        
        return [
            'family' => $family,
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif">Data Keluarga</h2>
            <p class="text-dark mb-0" style="font-size: 0.9rem;">Kelola profil keluarga dan data anak (balita) Anda.</p>
        </div>
        @if($family)
            <a href="{{ route('family.form', ['type' => 'member']) }}" class="btn rounded-pill px-4 fw-bold shadow-sm" style="background-color: #ffc107; color: #000;">
                <i class="fa-solid fa-plus me-1"></i> Tambah Balita
            </a>
        @endif
    </div>

    @if(!$family)
        <!-- Empty State untuk Keluarga -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-5 text-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-house-user fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark font-serif">Data Keluarga Belum Diisi</h4>
                <p class="text-secondary mx-auto" style="max-width: 500px;">Sebelum bisa mendaftarkan anak/balita, Anda wajib mengisi data Kepala Keluarga dan Alamat Domisili terlebih dahulu.</p>
                <a href="{{ route('family.form', ['type' => 'head']) }}" class="btn rounded-pill px-5 fw-bold mt-3 shadow-sm text-white" style="background-color: #009639;">
                    Lengkapi Data Kepala Keluarga
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <!-- Kartu Keluarga Info -->
            <div class="col-md-5">
                <div class="card border-0 rounded-4 h-100 shadow-sm" style="border-top: 5px solid #009639 !important;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center" style="font-size: 1.1rem;">
                            <i class="fa-solid fa-user-group me-2 text-primary-dark"></i> Profil Keluarga
                        </h5>
                    </div>
                    <div class="card-body px-4 pt-4">
                        <div class="mb-3">
                            <small class="text-dark d-block">Kepala Keluarga</small>
                            <span class="fw-bold text-dark fs-6">{{ $family->head_of_family_name }}</span>
                        </div>
                        <div class="row mb-4">
                            <div class="col-6">
                                <small class="text-dark d-block">Nomor KK</small>
                                <span class="fw-bold text-dark">{{ $family->no_kk ?? '-' }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-dark d-block">NIK Kepala Keluarga</small>
                                <span class="fw-bold text-dark">{{ $family->head_of_family_nik }}</span>
                            </div>
                        </div>
                        <hr class="text-muted opacity-10">
                        <div class="mb-4">
                            <small class="text-dark d-block mb-1">Alamat Domisili</small>
                            <p class="mb-0 text-dark fw-medium" style="font-size: 0.9rem; line-height: 1.5;">
                                RT {{ str_pad($family->address_rt, 3, '0', STR_PAD_LEFT) }} / RW {{ str_pad($family->address_rw, 3, '0', STR_PAD_LEFT) }}<br>
                                Kel/Desa. {{ $family->village->name ?? '-' }}, Kec. {{ $family->district->name ?? '-' }}<br>
                                {{ $family->city->name ?? '-' }}, {{ $family->province->name ?? '-' }}
                            </p>
                        </div>
                        <div class="mt-4 pt-2 text-center">
                            <a href="{{ route('family.form', ['type' => 'head']) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold" style="color: #009639; border-color: #009639;">
                                Edit Profil Keluarga
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Anggota / Balita -->
            <div class="col-md-7">
                <div class="card border-0 rounded-4 h-100 shadow-sm" style="border-top: 5px solid #009639 !important;">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0" style="font-size: 1.1rem;">Daftar Anggota Keluarga</h5>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        @if($family->members->isEmpty())
                            <div class="text-center my-auto p-5">
                                <i class="fa-solid fa-child-reaching text-muted opacity-50 mb-3" style="font-size: 4rem;"></i>
                                <h6 class="fw-bold text-dark fs-5">Belum ada anak/balita terdaftar</h6>
                                <p class="text-dark mb-4">Klik tombol "+ Tambah Balita" di kanan atas untuk mendaftarkan balita Anda.</p>
                                <a href="{{ route('family.form', ['type' => 'member']) }}" class="btn rounded-pill px-4 fw-bold shadow-sm" style="background-color: #fff3cd; color: #009639;">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah Balita
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-muted small">
                                        <tr>
                                            <th class="ps-3">Nama Lengkap</th>
                                            <th>Tgl Lahir / Umur</th>
                                            <th>Jenis Kelamin</th>
                                            <th class="text-end pe-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @foreach($family->members as $member)
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="fw-bold text-dark">{{ $member->name }}</div>
                                                    <div class="small text-muted">{{ $member->nik ?? '-' }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ \Carbon\Carbon::parse($member->birth_date)->translatedFormat('d M Y') }}</div>
                                                    @php
                                                        $umur = \Carbon\Carbon::parse($member->birth_date)->diff(now());
                                                    @endphp
                                                    <div class="small text-primary-dark fw-bold">{{ $umur->y }}th {{ $umur->m }}bln {{ $umur->d }}hr</div>
                                                </td>
                                                <td>
                                                    @if($member->gender === 'L')
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">Laki-laki</span>
                                                    @else
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3">Perempuan</span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-3">
                                                    <a href="{{ route('kms.chart', $member->id) }}" class="btn btn-sm btn-light rounded-circle" title="Lihat Grafik KMS">
                                                        <i class="fa-solid fa-chart-line text-primary-dark"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
