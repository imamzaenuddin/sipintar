<?php

use App\Models\FamilyMember;
use App\Models\KmsRecord;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public $memberId;
    public $member;
    
    // Form fields
    public $recorded_date;
    public $weight;
    public $height;
    public $head_circumference;
    public $lila;
    
    // ILP fields
    public $blood_pressure;
    public $belly_circumference;
    public $blood_sugar;
    public $uric_acid;
    public $cholesterol;
    public $manual_notes;
    
    public $schedule_id;
    public $schedule_name;
    public $is_date_locked = false;
    public $age_category = 'balita';
    
    public function mount($memberId)
    {
        $this->memberId = $memberId;
        $this->member = FamilyMember::findOrFail($memberId);
        
        $diff = \Carbon\Carbon::parse($this->member->birth_date)->diff(now());
        if ($diff->y < 6) {
            $this->age_category = 'balita';
        } elseif ($diff->y <= 18) {
            $this->age_category = 'remaja';
        } elseif ($diff->y <= 59) {
            $this->age_category = 'produktif';
        } else {
            $this->age_category = 'lansia';
        }
        
        $this->schedule_id = request()->query('schedule_id');
        if (!$this->schedule_id) {
            session()->flash('error', 'Anda wajib memilih Agenda Pelaksanaan (Jadwal) sebelum menginput data penimbangan.');
            return redirect()->route('targets.index');
        }
        
        $schedule = \App\Models\Schedule::find($this->schedule_id);
        if ($schedule) {
            $this->recorded_date = \Carbon\Carbon::parse($schedule->schedule_date)->format('Y-m-d');
            $this->schedule_name = $schedule->title;
            $this->is_date_locked = true;
        } else {
            session()->flash('error', 'Agenda Pelaksanaan tidak valid.');
            return redirect()->route('targets.index');
        }
        
        // Cek jika user ini kader atau admin
        if (!in_array(Auth::user()->role, ['kader', 'admin'])) {
            abort(403, 'Hanya Kader atau Admin yang dapat mengisi data penimbangan.');
        }
    }

    public function save()
    {
        $rules = [
            'recorded_date' => 'required|date',
            'weight' => 'required|numeric|min:0.5|max:150',
            'height' => 'required|numeric|min:20|max:250',
        ];

        if ($this->age_category === 'balita') {
            $rules['head_circumference'] = 'nullable|numeric|min:10|max:100';
            $rules['lila'] = 'nullable|numeric|min:5|max:30';
        } else {
            $rules['blood_pressure'] = 'nullable|string|max:10';
            $rules['belly_circumference'] = 'nullable|numeric|min:30|max:200';
            $rules['blood_sugar'] = 'nullable|integer|min:30|max:500';
            
            if ($this->age_category === 'lansia') {
                $rules['uric_acid'] = 'nullable|numeric|min:1|max:20';
                $rules['cholesterol'] = 'nullable|integer|min:50|max:400';
            }
        }

        $this->validate($rules);

        $auto_notes = [];
        $status_gizi = 'Gizi Baik';
        $z_score = 0;

        // Kalkulasi Balita (Sederhana Z-Score mock)
        if ($this->age_category === 'balita') {
            $height_m = $this->height / 100;
            $bmi = $this->weight / ($height_m * $height_m);
            if ($bmi < 13.5) {
                $status_gizi = 'Gizi Buruk';
                $z_score = -3;
                $auto_notes[] = "⚠️ Risiko Gizi Buruk terdeteksi. Segera konsul ke Puskesmas/Dokter Anak.";
            } elseif ($bmi < 14.5) {
                $status_gizi = 'Gizi Kurang';
                $z_score = -2;
                $auto_notes[] = "Risiko Gizi Kurang. Tingkatkan asupan protein hewani.";
            } elseif ($bmi > 18) {
                $status_gizi = 'Risiko Gizi Lebih';
                $z_score = 2;
                $auto_notes[] = "Risiko Gizi Lebih. Pantau aktivitas fisik balita.";
            } else {
                $status_gizi = 'Gizi Baik';
                $z_score = 0.5;
            }
        } 
        // Kalkulasi Dewasa (ILP)
        else {
            // Tensi
            if ($this->blood_pressure) {
                $bp = explode('/', $this->blood_pressure);
                if (count($bp) == 2) {
                    $sys = (int)$bp[0];
                    $dia = (int)$bp[1];
                    if ($sys >= 140 || $dia >= 90) {
                        $auto_notes[] = "⚠️ Tensi Tinggi (Hipertensi). Kurangi garam, rajin olahraga.";
                    } elseif ($sys >= 120 || $dia >= 80) {
                        $auto_notes[] = "Tensi Pra-Hipertensi. Pantau gaya hidup.";
                    }
                }
            }
            // Gula Darah
            if ($this->blood_sugar) {
                if ($this->blood_sugar >= 200) {
                    $auto_notes[] = "⚠️ Gula Darah Tinggi (Diabetes). Segera periksakan ke FKTP.";
                } elseif ($this->blood_sugar >= 140) {
                    $auto_notes[] = "Gula Darah Pra-Diabetes. Batasi konsumsi karbohidrat dan manis.";
                }
            }
            // Asam Urat & Kolesterol
            if ($this->uric_acid && $this->uric_acid > 7.0) {
                $auto_notes[] = "Asam Urat Tinggi. Kurangi jeroan & kacang-kacangan.";
            }
            if ($this->cholesterol && $this->cholesterol >= 200) {
                $auto_notes[] = "⚠️ Kolesterol Tinggi. Kurangi makanan bersantan & gorengan.";
            }
        }

        $final_notes = implode(" ", $auto_notes);
        if (!empty($this->manual_notes)) {
            $final_notes .= "\n\nCatatan Kader: " . $this->manual_notes;
        }

        KmsRecord::create([
            'family_member_id' => $this->memberId,
            'recorded_date' => $this->recorded_date,
            'weight' => $this->weight,
            'height' => $this->height,
            'head_circumference' => $this->age_category === 'balita' ? $this->head_circumference : null,
            'lila' => $this->age_category === 'balita' ? $this->lila : null,
            'z_score' => $z_score,
            'status_gizi' => $status_gizi,
            'recorder_id' => Auth::id(),
            'blood_pressure' => $this->blood_pressure,
            'belly_circumference' => $this->belly_circumference,
            'blood_sugar' => $this->blood_sugar,
            'uric_acid' => $this->uric_acid,
            'cholesterol' => $this->cholesterol,
            'examination_notes' => $final_notes,
        ]);

        session()->flash('success', 'Data penimbangan berhasil disimpan.');
        
        if ($this->schedule_id) {
            return redirect()->route('targets.index');
        }
        
        return redirect()->route('kms.chart', ['memberId' => $this->memberId]);
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif">Pencatatan KMS</h2>
            <p class="text-dark mb-0">Input data pengukuran balita untuk KMS Digital.</p>
        </div>
        <a href="{{ route('kms.chart', $memberId) }}" class="btn btn-light rounded-pill px-4 shadow-sm text-primary-dark fw-bold border">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Grafik
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 rounded-4 shadow-sm" style="border-top: 5px solid #009639 !important;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                    <h5 class="fw-bold text-dark font-serif mb-1">Form Pengukuran ILP</h5>
                    @if($schedule_name)
                    <div class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1 mb-2">
                        <i class="fa-solid fa-calendar-check me-1"></i> Agenda: {{ $schedule_name }}
                    </div>
                    @endif
                    @php
                        $umurSaatIni = \Carbon\Carbon::parse($member->birth_date)->diff(now());
                    @endphp
                    <p class="text-muted small mb-0">{{ ucfirst($age_category) }}: <strong class="text-primary-dark fs-6">{{ $member->name }}</strong> (Umur: {{ $umurSaatIni->y }}th {{ $umurSaatIni->m }}bln {{ $umurSaatIni->d }}hr)</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    @if (session()->has('success'))
                        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success rounded-3 mb-4">
                            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit="save">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Tanggal Pengukuran <span class="text-danger">*</span></label>
                            <input type="date" wire:model="recorded_date" class="form-control rounded-3 py-2 bg-light @error('recorded_date') is-invalid @enderror" {{ $is_date_locked ? 'readonly' : '' }}>
                            @if($is_date_locked)
                                <div class="form-text text-success"><i class="fa-solid fa-lock me-1"></i> Tanggal otomatis disesuaikan dengan Agenda Pelaksanaan.</div>
                            @endif
                            @error('recorded_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Berat Badan (kg) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" wire:model="weight" class="form-control rounded-start-3 py-2 bg-light @error('weight') is-invalid @enderror" placeholder="Cth: {{ $age_category == 'balita' ? '10.5' : '65.0' }}">
                                    <span class="input-group-text bg-white text-muted">kg</span>
                                    @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tinggi/Panjang Badan (cm) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" wire:model="height" class="form-control rounded-start-3 py-2 bg-light @error('height') is-invalid @enderror" placeholder="Cth: {{ $age_category == 'balita' ? '85.0' : '160.5' }}">
                                    <span class="input-group-text bg-white text-muted">cm</span>
                                    @error('height') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @if($age_category === 'balita')
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Lingkar Kepala (cm) <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" wire:model="head_circumference" class="form-control rounded-start-3 py-2 bg-light @error('head_circumference') is-invalid @enderror" placeholder="Contoh: 45.2">
                                    <span class="input-group-text bg-white text-muted">cm</span>
                                    @error('head_circumference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">LILA (cm) <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" wire:model="lila" class="form-control rounded-start-3 py-2 bg-light @error('lila') is-invalid @enderror" placeholder="Contoh: 15.0">
                                    <span class="input-group-text bg-white text-muted">cm</span>
                                    @error('lila') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- Form Remaja/Produktif/Lansia -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Tekanan Darah <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="text" wire:model="blood_pressure" class="form-control rounded-start-3 py-2 bg-light @error('blood_pressure') is-invalid @enderror" placeholder="Cth: 120/80">
                                    <span class="input-group-text bg-white text-muted">mmHg</span>
                                    @error('blood_pressure') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Lingkar Perut (cm) <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" wire:model="belly_circumference" class="form-control rounded-start-3 py-2 bg-light @error('belly_circumference') is-invalid @enderror" placeholder="Cth: 85.5">
                                    <span class="input-group-text bg-white text-muted">cm</span>
                                    @error('belly_circumference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-{{ $age_category == 'lansia' ? '4' : '12' }}">
                                <label class="form-label fw-bold text-dark">Gula Darah <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model="blood_sugar" class="form-control rounded-start-3 py-2 bg-light @error('blood_sugar') is-invalid @enderror" placeholder="Cth: 110">
                                    <span class="input-group-text bg-white text-muted">mg/dL</span>
                                    @error('blood_sugar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @if($age_category === 'lansia')
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Asam Urat <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.1" wire:model="uric_acid" class="form-control rounded-start-3 py-2 bg-light @error('uric_acid') is-invalid @enderror" placeholder="Cth: 6.5">
                                    <span class="input-group-text bg-white text-muted">mg/dL</span>
                                    @error('uric_acid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Kolesterol <span class="text-muted fw-normal">(Opsional)</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model="cholesterol" class="form-control rounded-start-3 py-2 bg-light @error('cholesterol') is-invalid @enderror" placeholder="Cth: 190">
                                    <span class="input-group-text bg-white text-muted">mg/dL</span>
                                    @error('cholesterol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Catatan Tambahan Kader <span class="text-muted fw-normal">(Opsional)</span></label>
                            <textarea wire:model="manual_notes" class="form-control rounded-3 py-2 bg-light @error('manual_notes') is-invalid @enderror" rows="3" placeholder="Contoh: Balita rewel saat ditimbang, atau lansia mengeluh pusing..."></textarea>
                            <div class="form-text text-muted">Sistem akan secara otomatis menyertakan kesimpulan Kemenkes/WHO ke dalam riwayat, Anda dapat menambahkan catatan ekstra di sini.</div>
                            @error('manual_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" class="btn btn-lg rounded-pill fw-bold text-white shadow" style="background-color: #009639;">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Data Pengukuran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
