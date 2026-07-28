<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.dashboard')] class extends Component {
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';

    // Form fields
    public $userId;
    public $name;
    public $nik;
    public $phone;
    public $role = 'warga';
    public $password;
    
    // UI State
    public $isEditMode = false;
    public $userToDelete;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses khusus Admin.');
        }
    }

    public function resetForm()
    {
        $this->reset(['userId', 'name', 'nik', 'phone', 'role', 'password', 'isEditMode']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $user = User::findOrFail($id);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->nik = $user->nik;
        $this->phone = $user->phone;
        $this->role = $user->role;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'role' => 'required|in:warga,kader,admin',
        ];

        // Validasi NIK unik kecuali untuk user yang sedang diubah
        if ($this->isEditMode) {
            $rules['nik'] = 'required|string|size:16|unique:users,nik,' . $this->userId;
            // Password opsional saat edit
            $rules['password'] = 'nullable|string|min:6';
        } else {
            $rules['nik'] = 'required|string|size:16|unique:users,nik';
            $rules['password'] = 'required|string|min:6';
        }

        $this->validate($rules);

        if ($this->isEditMode) {
            // Edit User
            $user = User::findOrFail($this->userId);
            
            // Cegah admin mengubah rolenya sendiri menjadi non-admin
            if ($user->id == auth()->id() && $this->role != 'admin') {
                session()->flash('error', 'Anda tidak dapat menurunkan hak akses Anda sendiri.');
                return;
            }

            $user->name = $this->name;
            $user->nik = $this->nik;
            $user->phone = $this->phone;
            $user->role = $this->role;
            
            if (!empty($this->password)) {
                $user->password = \Illuminate\Support\Facades\Hash::make($this->password);
            }
            
            $user->save();
            session()->flash('success', 'Data pengguna berhasil diperbarui.');
        } else {
            // Tambah User
            User::create([
                'name' => $this->name,
                'nik' => $this->nik,
                'phone' => $this->phone,
                'role' => $this->role,
                'password' => \Illuminate\Support\Facades\Hash::make($this->password),
            ]);
            session()->flash('success', 'Pengguna baru berhasil ditambahkan.');
        }

        $this->dispatch('close-modal');
    }

    public function confirmDelete($id)
    {
        $this->userToDelete = $id;
    }

    public function delete()
    {
        if ($this->userToDelete) {
            // Cegah menghapus diri sendiri
            if ($this->userToDelete == auth()->id()) {
                session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
                $this->dispatch('close-modal');
                return;
            }
            
            User::findOrFail($this->userToDelete)->delete();
            session()->flash('success', 'Pengguna berhasil dihapus beserta seluruh datanya.');
            $this->userToDelete = null;
            $this->dispatch('close-modal');
        }
    }

    public function updateRole($userId, $newRole)
    {
        if ($userId == auth()->id() && $newRole != 'admin') {
            session()->flash('error', 'Anda tidak dapat menurunkan hak akses diri sendiri.');
            return;
        }

        $user = User::findOrFail($userId);
        
        if (in_array($newRole, ['warga', 'kader', 'admin'])) {
            $user->role = $newRole;
            $user->save();
            session()->flash('success', "Role pengguna {$user->name} berhasil diubah menjadi " . ucfirst($newRole) . ".");
        }
    }

    public function with()
    {
        return [
            'users' => User::where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nik', 'like', '%'.$this->search.'%')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 font-serif text-dark">Manajemen Pengguna</h2>
            <p class="text-muted mb-0">Kelola daftar warga terdaftar dan atur hak akses Kader/Admin.</p>
        </div>
        <div>
            <button wire:click="create" data-bs-toggle="modal" data-bs-target="#userModal" class="btn rounded-pill px-4 fw-bold shadow-sm text-white" style="background-color: #009639;">
                <i class="fa-solid fa-plus me-2"></i> Tambah Pengguna
            </button>
        </div>
    </div>

    <!-- Notifikasi -->
    @if (session()->has('success'))
        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success rounded-3 d-flex align-items-center mb-4 shadow-sm">
            <i class="fa-solid fa-circle-check fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 d-flex align-items-center mb-4 shadow-sm">
            <i class="fa-solid fa-circle-exclamation fs-4 me-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4" style="border-top: 5px solid #009639 !important;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="fw-bold text-dark font-serif mb-0"><i class="fa-solid fa-users text-primary-dark me-2"></i> Daftar Akun Terdaftar</h5>
                </div>
                <div class="col-md-6 mt-3 mt-md-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-start-0" placeholder="Cari berdasarkan Nama atau NIK...">
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
                            <th>Info Pengguna</th>
                            <th>Kontak / Domisili</th>
                            <th>Hak Akses (Role)</th>
                            <th class="text-center">Tanggal Daftar</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($users as $index => $u)
                            <tr>
                                <td class="ps-4 text-muted">{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-light rounded-circle d-flex align-items-center justify-content-center me-3 text-primary-dark fw-bold shadow-sm" style="width: 40px; height: 40px;">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">{{ $u->name }}</h6>
                                            <span class="text-muted small font-monospace"><i class="fa-regular fa-id-card me-1"></i> {{ $u->nik }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1"><i class="fa-brands fa-whatsapp text-success me-1"></i> {{ $u->phone_number ?? '-' }}</div>
                                </td>
                                <td>
                                    <select wire:change="updateRole({{ $u->id }}, $event.target.value)" 
                                            class="form-select form-select-sm fw-bold rounded-pill shadow-sm cursor-pointer
                                            @if($u->role === 'admin') bg-danger bg-opacity-10 text-danger border-danger
                                            @elseif($u->role === 'kader') bg-warning bg-opacity-25 text-dark border-warning
                                            @else bg-light text-muted border-secondary @endif" 
                                            style="width: 140px;">
                                        <option value="warga" @if($u->role === 'warga') selected @endif>Warga</option>
                                        <option value="kader" @if($u->role === 'kader') selected @endif>Kader Posyandu</option>
                                        <option value="admin" @if($u->role === 'admin') selected @endif>Admin/Bidan</option>
                                    </select>
                                    
                                    <div wire:loading wire:target="updateRole({{ $u->id }}, $event.target.value)" class="small text-muted mt-1">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...
                                    </div>
                                </td>
                                <td class="text-center text-muted small">
                                    {{ $u->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $u->id }})" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-sm btn-light text-primary border me-1 shadow-sm" title="Edit Pengguna">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    @if($u->id !== auth()->id())
                                    <button wire:click="confirmDelete({{ $u->id }})" data-bs-toggle="modal" data-bs-target="#deleteModal" class="btn btn-sm btn-light text-danger border shadow-sm" title="Hapus Pengguna">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fa-solid fa-users-slash text-muted opacity-25 mb-3" style="font-size: 3rem;"></i>
                                    <h6 class="text-dark fw-bold">Tidak ada data ditemukan</h6>
                                    <p class="text-muted small">Coba ubah kata kunci pencarian Anda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-white border-top-0 pt-3 pb-4 px-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form Pengguna -->
    <div wire:ignore.self class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form wire:submit="save">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold font-serif">{{ $isEditMode ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nik" class="form-control bg-light @error('nik') is-invalid @enderror" placeholder="16 Digit NIK" maxlength="16">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control bg-light @error('name') is-invalid @enderror" placeholder="Sesuai KTP">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. Handphone / WhatsApp</label>
                            <input type="text" wire:model="phone" class="form-control bg-light @error('phone') is-invalid @enderror" placeholder="08123456789">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hak Akses <span class="text-danger">*</span></label>
                            <select wire:model="role" class="form-select bg-light @error('role') is-invalid @enderror">
                                <option value="warga">Warga</option>
                                <option value="kader">Kader Posyandu</option>
                                <option value="admin">Admin / Bidan</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold">Kata Sandi (Password) {!! $isEditMode ? '<span class="text-muted fw-normal">(Kosongkan jika tidak ingin diubah)</span>' : '<span class="text-danger">*</span>' !!}</label>
                            <input type="password" wire:model="password" class="form-control bg-light @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-pill px-4 fw-bold" style="background-color: #009639;">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div wire:ignore.self class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
                <i class="fa-solid fa-triangle-exclamation text-danger mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mb-2">Hapus Pengguna?</h5>
                <p class="text-muted small mb-4">Tindakan ini akan menghapus akun beserta <strong>seluruh riwayat keluarga dan KMS</strong> yang terkait. Aksi ini tidak dapat dibatalkan.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="button" wire:click="delete" class="btn btn-danger rounded-pill px-4 fw-bold">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', () => {
                var userModal = bootstrap.Modal.getInstance(document.getElementById('userModal'));
                if (userModal) userModal.hide();
                
                var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                if (deleteModal) deleteModal.hide();
            });
        });
    </script>
</div>
