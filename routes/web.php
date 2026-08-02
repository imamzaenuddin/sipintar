<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Volt::route('/login', 'auth.login')->name('login');
    Volt::route('/register', 'auth.register')->name('register');
});

Route::middleware('auth')->group(function () {
    Volt::route('/dashboard/warga', 'dashboard.warga')->name('dashboard.warga');
    Volt::route('/dashboard/admin', 'dashboard.admin')->name('dashboard.admin');
    Volt::route('/family', 'family.index')->name('family.index');
    Volt::route('/family/form', 'family.form')->name('family.form');

    // KMS Module
    Volt::route('/kms/chart/{memberId}', 'kms.chart')->name('kms.chart');
    Volt::route('/kms/form/{memberId}', 'kms.form')->name('kms.form');

    // Schedule Module
    Volt::route('/schedule', 'schedule.index')->name('schedule.index');
    Volt::route('/schedule/manage', 'schedule.manage')->name('schedule.manage');
    
    // Halaman Data Sasaran Posyandu (Balita, Lansia, dll) khusus Kader & Admin
    Volt::route('/targets', 'targets.index')->name('targets.index');

    // User Management Module
    Volt::route('/users', 'users.index')->name('users.index');

    // Reporting Module
    Volt::route('/reports', 'reports.index')->name('reports.index');
    Route::get('/reports/print', function(\Illuminate\Http\Request $request) {
        if (!in_array(auth()->user()->role, ['kader', 'admin'])) abort(403);
        
        $month = $request->query('month', now()->format('m'));
        $year = $request->query('year', now()->format('Y'));
        $category = $request->query('category', '');

        $records = \App\Models\KmsRecord::with(['familyMember.family.user'])
            ->whereMonth('recorded_date', $month)
            ->whereYear('recorded_date', $year)
            ->when($category, function ($q) use ($category) {
                $q->whereHas('familyMember', function ($sub) use ($category) {
                    if ($category == 'balita') {
                        $sub->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, kms_records.recorded_date) < 5');
                    } elseif ($category == 'remaja') {
                        $sub->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, kms_records.recorded_date) BETWEEN 10 AND 18');
                    } elseif ($category == 'usia_produktif') {
                        $sub->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, kms_records.recorded_date) BETWEEN 15 AND 59');
                    } elseif ($category == 'lansia') {
                        $sub->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, kms_records.recorded_date) >= 60');
                    }
                });
            })
            ->orderBy('recorded_date', 'desc')
            ->get();

        return view('reports.print', compact('records'));
    })->name('reports.print');

    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'warga') {
            return redirect()->route('dashboard.warga');
        }
        return redirect()->route('dashboard.admin');
    })->name('dashboard');

    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Quick Role Switcher (Untuk Testing)
    Route::post('/switch-role', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $user->role = $request->role;
        $user->save();
        return redirect()->route('dashboard')->with('success', 'Peran berhasil diubah menjadi ' . ucfirst($request->role) . '.');
    })->name('switch.role');
});
