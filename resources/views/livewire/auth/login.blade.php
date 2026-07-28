<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public string $nik = '';
    public string $password = '';
    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'nik' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['nik' => $this->nik, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        throw ValidationException::withMessages([
            'nik' => 'NIK atau password salah.',
        ]);
    }
}; ?>

<div>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card glass border-0 rounded-4 shadow-sm p-4 w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" height="60" class="mb-3 drop-shadow">
                <h3 class="fw-bold text-primary">SiPintar</h3>
                <p class="text-muted">Masuk ke akun Anda</p>
            </div>
            
            <form wire:submit="login">
                <div class="mb-3">
                    <label class="form-label">NIK</label>
                    <input wire:model="nik" type="text" class="form-control" required autofocus autocomplete="username">
                    @error('nik') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input wire:model="password" type="password" class="form-control" required>
                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3 form-check">
                    <input wire:model="remember" type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat Saya</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <span wire:loading.remove>Masuk</span>
                    <span wire:loading>Memproses...</span>
                </button>
                
                <div class="text-center mt-3">
                    <p class="small">Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-bold text-secondary">Daftar sekarang</a></p>
                </div>
            </form>
        </div>
    </div>
</div>
