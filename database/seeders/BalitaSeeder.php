<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FamilyMember;
use App\Models\KmsRecord;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class BalitaSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');

        // 1. Cari data Ibu
        // Di sistem ini, diasumsikan Ibu adalah perempuan dengan relasi suami_istri atau kepala_keluarga
        $mothers = FamilyMember::where('gender', 'P')
            ->whereIn('relation', ['suami_istri', 'kepala_keluarga'])
            ->get();

        if ($mothers->isEmpty()) {
            $this->command->warn('Tidak ada data Ibu di dalam sistem.');
            return;
        }

        // 2. Tentukan 80% dari jumlah ibu
        $count = (int) round($mothers->count() * 0.8);
        
        if ($count === 0) {
            $this->command->warn('Jumlah Ibu terlalu sedikit untuk diambil 80%-nya.');
            return;
        }

        $selectedMothers = $mothers->random($count);
        
        // 3. Cari kader untuk mengisi kolom recorder_id
        $kader = User::where('role', 'kader')->first();
        $kaderId = $kader ? $kader->id : null;

        // 4. Ambil jadwal posyandu balita atau gabungan
        $schedules = Schedule::where('title', 'like', '%Balita%')
            ->orWhere('title', 'like', '%Gabungan%')
            ->get();

        $this->command->info("Membuat Balita untuk {$count} Ibu...");

        foreach ($selectedMothers as $mother) {
            // Umur Balita (0 - 60 Bulan)
            $ageInMonths = rand(1, 60);
            $birthDate = Carbon::now()->subMonths($ageInMonths);

            // Buat Data Balita
            $balita = FamilyMember::create([
                'family_id' => $mother->family_id,
                'nik' => $faker->unique()->numerify('3275############'),
                'name' => $faker->firstName . ' (Anak)',
                'birth_date' => $birthDate,
                'gender' => $faker->randomElement(['L', 'P']),
                'relation' => 'anak',
            ]);

            // 5. Buat Hasil Pemeriksaan KMS sesuai jadwal
            foreach ($schedules as $schedule) {
                $scheduleDate = Carbon::parse($schedule->schedule_date);
                
                // Pastikan bayi sudah lahir saat jadwal pelaksanaan tersebut
                if ($scheduleDate->greaterThanOrEqualTo($birthDate)) {
                    $umurBulanSaatDiukur = $birthDate->diffInMonths($scheduleDate);
                    
                    // Pastikan saat jadwal, usianya masih balita (<= 60 bulan)
                    if ($umurBulanSaatDiukur <= 60) {
                        $status = $faker->randomElement(['Gizi Baik', 'Gizi Baik', 'Gizi Kurang', 'Gizi Lebih / Obesitas']);
                        
                        KmsRecord::create([
                            'family_member_id' => $balita->id,
                            'recorded_date' => $scheduleDate->format('Y-m-d'),
                            'recorder_id' => $kaderId,
                            'weight' => round(3.5 + ($umurBulanSaatDiukur * 0.2) + $faker->randomFloat(1, -0.5, 1.5), 1),
                            'height' => round(50 + ($umurBulanSaatDiukur * 1.5) + $faker->randomFloat(1, -2, 3), 1),
                            'head_circumference' => round($faker->randomFloat(1, 35, 45), 1),
                            'lila' => round($faker->randomFloat(1, 10, 16), 1),
                            'status_gizi' => $status,
                            'z_score' => ($status == 'Gizi Kurang') ? -2.5 : (($status == 'Gizi Baik') ? 0.5 : 2.5),
                        ]);
                    }
                }
            }
        }

        $this->command->info("Seeder selesai. Telah dibuat {$count} data Balita beserta hasil pemeriksaannya sesuai jadwal.");
    }
}
