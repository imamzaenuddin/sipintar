<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public string $nik = '';
    public string $name = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $validated = $this->validate([
            'nik' => ['required', 'string', 'size:16', 'unique:'.User::class],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'warga';

        $user = User::create($validated);

        event(new Registered($user));
        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false));
    }
}; ?>

<div>
    <div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 100vh;">
        <div class="card glass border-0 rounded-4 shadow-sm p-4 w-100" style="max-width: 450px;">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" height="60" class="mb-3 drop-shadow">
                <h3 class="fw-bold text-primary">SiPintar</h3>
                <p class="text-muted">Pendaftaran Akun Warga</p>
            </div>
            
            <form wire:submit="register">
                <div class="mb-3">
                    <label class="form-label">Nomor Induk Kependudukan (NIK)</label>
                    <input wire:model="nik" type="text" class="form-control" required autofocus autocomplete="username" maxlength="16">
                    @error('nik') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input wire:model="name" type="text" class="form-control" required autocomplete="name">
                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nomor WhatsApp Aktif</label>
                    <input wire:model="phone" type="text" class="form-control" required autocomplete="tel">
                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input wire:model="password" type="password" class="form-control" required autocomplete="new-password">
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input wire:model="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
                    @error('password_confirmation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                
                <button type="submit" class="btn btn-secondary w-100 fw-bold mt-2">
                    <span wire:loading.remove>Daftar Sekarang</span>
                    <span wire:loading>Memproses...</span>
                </button>
                
                <div class="text-center mt-3">
                    <p class="small">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none fw-bold text-primary">Masuk di sini</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
