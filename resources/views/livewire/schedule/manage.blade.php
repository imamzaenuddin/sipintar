<?php

use App\Models\Schedule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

new #[Layout('components.layouts.dashboard')] class extends Component {
    public $schedules;
    
    // Form fields
    public $title;
    public $description;
    public $schedule_date;
    public $location;
    public $target_groups = []; // balita, remaja, produktif, lansia
    
    // UI state
    public $isEditing = false;
    public $editId;

    public function mount()
    {
        if (!in_array(Auth::user()->role, ['kader', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }
        $this->loadSchedules();
    }

    public function loadSchedules()
    {
        $this->schedules = Schedule::orderBy('schedule_date', 'asc')->get();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'schedule_date' => 'required|date',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_groups' => 'required|array|min:1',
        ], [
            'target_groups.required' => 'Pilih minimal satu kelompok sasaran.',
            'target_groups.min' => 'Pilih minimal satu kelompok sasaran.'
        ]);

        if ($this->isEditing) {
            $schedule = Schedule::findOrFail($this->editId);
            $schedule->update([
                'title' => $this->title,
                'schedule_date' => $this->schedule_date,
                'location' => $this->location,
                'description' => $this->description,
                'target_groups' => $this->target_groups,
            ]);
            session()->flash('success', 'Jadwal berhasil diperbarui.');
        } else {
            Schedule::create([
                'title' => $this->title,
                'schedule_date' => $this->schedule_date,
                'location' => $this->location,
                'description' => $this->description,
                'target_groups' => $this->target_groups,
                'created_by' => Auth::id(),
            ]);
            session()->flash('success', 'Jadwal baru berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->loadSchedules();
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $this->editId = $schedule->id;
        $this->title = $schedule->title;
        $this->schedule_date = \Carbon\Carbon::parse($schedule->schedule_date)->format('Y-m-d\TH:i');
        $this->location = $schedule->location;
        $this->description = $schedule->description;
        $this->target_groups = $schedule->target_groups ?? [];
        $this->isEditing = true;
    }

    public function delete($id)
    {
        Schedule::findOrFail($id)->delete();
        session()->flash('success', 'Jadwal berhasil dihapus.');
        $this->loadSchedules();
    }

    public function resetForm()
    {
        $this->reset(['title', 'schedule_date', 'location', 'description', 'target_groups', 'isEditing', 'editId']);
    }

    public function broadcastWA($id)
    {
        // SIMULASI WHATSAPP BROADCAST
        // Di sistem nyata, ini akan memanggil Job Queue untuk menembak API Fonnte/Wablas/Twilio.
        $schedule = Schedule::findOrFail($id);
        
        // Simulasi delay (opsional) atau langsung tampilkan sukses
        session()->flash('wa_success', "Berhasil! Simulasi undangan WhatsApp untuk acara '{$schedule->title}' telah terkirim ke 45 Warga di RT/RW setempat.");
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif">Kelola Jadwal Posyandu</h2>
            <p class="text-dark mb-0">Buat jadwal kegiatan rutin dan kirimkan undangan via WhatsApp.</p>
        </div>
    </div>

    @if (session()->has('wa_success'))
        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success rounded-4 mb-4 d-flex align-items-center p-4 shadow-sm">
            <i class="fa-brands fa-whatsapp fs-1 me-3"></i> 
            <div>
                <h5 class="fw-bold mb-1">Broadcast WhatsApp Terkirim</h5>
                <p class="mb-0">{{ session('wa_success') }}</p>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Form Tambah/Edit Jadwal -->
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-top: 5px solid #009639 !important;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0 font-serif">{{ $isEditing ? 'Edit Jadwal' : 'Tambah Jadwal Baru' }}</h5>
                </div>
                <div class="card-body p-4">
                    @if (session()->has('success'))
                        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success rounded-3 py-2 mb-3">
                            <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit="save">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control rounded-3 bg-light @error('title') is-invalid @enderror" placeholder="Cth: Posyandu Balita & Imunisasi">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Tanggal & Waktu <span class="text-danger">*</span></label>
                            <input type="datetime-local" wire:model="schedule_date" class="form-control rounded-3 bg-light @error('schedule_date') is-invalid @enderror">
                            @error('schedule_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-dark">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" wire:model="location" class="form-control rounded-3 bg-light @error('location') is-invalid @enderror" placeholder="Cth: Balai Warga RW 09">
                            @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Catatan Tambahan (Opsional)</label>
                            <textarea wire:model="description" class="form-control rounded-3 bg-light @error('description') is-invalid @enderror" rows="2" placeholder="Cth: Bawa buku KMS dan fotokopi KK"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark">Kelompok Sasaran (ILP) <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2 @error('target_groups') is-invalid @enderror">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="target_groups" value="balita" id="tg_balita">
                                    <label class="form-check-label" for="tg_balita">Balita (0-5 Thn)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="target_groups" value="remaja" id="tg_remaja">
                                    <label class="form-check-label" for="tg_remaja">Remaja (10-18 Thn)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="target_groups" value="produktif" id="tg_produktif">
                                    <label class="form-check-label" for="tg_produktif">Usia Produktif (19-59 Thn)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="target_groups" value="lansia" id="tg_lansia">
                                    <label class="form-check-label" for="tg_lansia">Lansia (≥60 Thn)</label>
                                </div>
                            </div>
                            @error('target_groups') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn rounded-pill fw-bold text-white shadow-sm" style="background-color: #009639;">
                                {{ $isEditing ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                            </button>
                            @if($isEditing)
                                <button type="button" wire:click="resetForm" class="btn btn-light rounded-pill fw-bold text-muted border">
                                    Batal
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Jadwal -->
        <div class="col-md-8">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="border-top: 5px solid #ffc107 !important;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0 font-serif">Daftar Jadwal Kegiatan</h5>
                </div>
                <div class="card-body p-4">
                    @if($schedules->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa-solid fa-calendar-xmark text-muted opacity-25 mb-3" style="font-size: 4rem;"></i>
                            <h5 class="text-dark fw-bold">Belum Ada Jadwal</h5>
                            <p class="text-muted">Jadwal kegiatan yang ditambahkan akan muncul di sini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th>Kegiatan</th>
                                        <th>Waktu & Lokasi</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @foreach($schedules as $schedule)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $schedule->title }}</div>
                                                <div class="small text-muted mb-1">{{ Str::limit($schedule->description, 50) }}</div>
                                                <div class="d-flex gap-1 flex-wrap mt-1">
                                                    @if(is_array($schedule->target_groups))
                                                        @foreach($schedule->target_groups as $tg)
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill" style="font-size: 0.7rem;">{{ ucfirst($tg) }}</span>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-primary-dark fw-bold mb-1">
                                                    <i class="fa-regular fa-clock me-1"></i> {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('d M Y, H:i') }}
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fa-solid fa-location-dot me-1"></i> {{ $schedule->location }}
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <button wire:click="broadcastWA({{ $schedule->id }})" class="btn btn-sm rounded-pill px-3 fw-bold shadow-sm mb-1" style="background-color: #25D366; color: white;" title="Kirim Notifikasi WA ke Warga">
                                                    <i class="fa-brands fa-whatsapp me-1"></i> Broadcast WA
                                                </button>
                                                <div>
                                                    <button wire:click="edit({{ $schedule->id }})" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Edit">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button wire:click="delete({{ $schedule->id }})" wire:confirm="Yakin ingin menghapus jadwal ini?" class="btn btn-sm btn-outline-danger rounded-circle" title="Hapus">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
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
</div>
