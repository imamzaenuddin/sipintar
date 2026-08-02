<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\KmsRecord;
use App\Models\Schedule;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID');
        $password = Hash::make('password123');
        $now = Carbon::now();

        $this->command->info('Mulai generate dummy data (2 Kader, 50 Warga mix usia, Jadwal ILP)...');

        // 1. Buat 2 Kader
        $kaders = [];
        for ($i = 1; $i <= 2; $i++) {
            $kader = User::create([
                'name' => 'Kader ' . $faker->firstNameFemale,
                'nik' => $faker->unique()->numerify('3275############'),
                'phone' => $faker->numerify('08##########'),
                'password' => $password,
                'role' => 'kader',
            ]);
            $kaders[] = $kader->id;
        }

        // 2. Buat 15 Keluarga (User Warga & Kepala Keluarga)
        $families = [];
        $allMembers = [];
        for ($i = 1; $i <= 15; $i++) {
            $warga = User::create([
                'name' => $faker->name,
                'nik' => $faker->unique()->numerify('3275############'),
                'phone' => $faker->numerify('08##########'),
                'password' => $password,
                'role' => 'warga',
            ]);

            $family = Family::create([
                'user_id' => $warga->id,
                'head_of_family_nik' => $warga->nik,
                'head_of_family_name' => $warga->name,
                'province_code' => '32',
                'city_code' => '3275',
                'district_code' => '327501',
                'village_code' => '3275011001',
                'address_rt' => str_pad(rand(1, 15), 3, '0', STR_PAD_LEFT),
                'address_rw' => str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
            ]);
            $families[] = $family->id;

            // Kepala Keluarga
            $member = FamilyMember::create([
                'family_id' => $family->id,
                'nik' => $warga->nik,
                'name' => $warga->name,
                'birth_date' => Carbon::now()->subYears(rand(25, 55)),
                'gender' => $faker->randomElement(['L', 'P']),
                'relation' => 'kepala_keluarga',
            ]);
            $allMembers[] = $member;
        }

        // 3. Tambahkan 35 Anggota Keluarga (Mix Balita, Anak, Remaja, Lansia, dll)
        // Total 15 KK + 35 Anggota = 50 orang
        for ($i = 1; $i <= 35; $i++) {
            $age = rand(0, 75); // Umur random dari 0 sampai 75 tahun
            $relation = 'anak';
            if ($age > 60) $relation = 'orang_tua';
            elseif ($age >= 25) $relation = 'suami_istri';

            $member = FamilyMember::create([
                'family_id' => $faker->randomElement($families),
                'nik' => $faker->unique()->numerify('3275############'),
                'name' => $faker->name,
                'birth_date' => Carbon::now()->subYears($age)->subMonths(rand(0, 11)),
                'gender' => $faker->randomElement(['L', 'P']),
                'relation' => $relation,
            ]);
            $allMembers[] = $member;
        }

        // 4. Buat Jadwal 3 Bulan Terakhir & Isi Data Pengukuran (KMS)
        $kategoriKegiatan = [
            ['title' => 'Posyandu Balita & Imunisasi', 'type' => 'balita'],
            ['title' => 'Posyandu Remaja', 'type' => 'remaja'],
            ['title' => 'Posbindu Usia Produktif', 'type' => 'dewasa'],
            ['title' => 'Posyandu Lansia Sehat', 'type' => 'lansia'],
            ['title' => 'Posyandu Keluarga (Gabungan)', 'type' => 'gabungan'],
        ];

        for ($m = 0; $m <= 2; $m++) {
            $targetMonth = Carbon::now()->subMonths($m);

            foreach ($kategoriKegiatan as $keg) {
                $scheduleDate = $targetMonth->copy()->startOfMonth()->addDays(rand(5, 25))->setHour(8)->setMinute(0);

                $schedule = Schedule::create([
                    'title' => $keg['title'] . ' - ' . $scheduleDate->translatedFormat('F Y'),
                    'description' => 'Kegiatan rutin ' . strtolower($keg['title']) . ' tingkat RW.',
                    'schedule_date' => $scheduleDate,
                    'location' => 'Balai Warga RW ' . str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
                    'created_by' => $kaders[0],
                ]);

                foreach ($allMembers as $member) {
                    $ageInYears = $member->birth_date->diffInYears($scheduleDate);
                    
                    // Filter berdasarkan target kegiatan (Integrasi Layanan Primer / ILP)
                    $isBalita = $ageInYears <= 5;
                    $isRemaja = $ageInYears >= 12 && $ageInYears <= 18;
                    $isDewasa = $ageInYears >= 19 && $ageInYears <= 59;
                    $isLansia = $ageInYears >= 60;

                    $shouldRecord = false;
                    if ($isBalita && in_array($keg['type'], ['balita', 'gabungan'])) $shouldRecord = true;
                    if ($isRemaja && in_array($keg['type'], ['remaja', 'gabungan'])) $shouldRecord = true;
                    if ($isDewasa && in_array($keg['type'], ['dewasa', 'gabungan'])) $shouldRecord = true;
                    if ($isLansia && in_array($keg['type'], ['lansia', 'gabungan'])) $shouldRecord = true;

                    // Pastikan warga sudah lahir saat jadwal tersebut
                    if ($shouldRecord && $scheduleDate->greaterThanOrEqualTo($member->birth_date)) {
                        $data = [
                            'family_member_id' => $member->id,
                            'recorded_date' => $scheduleDate->format('Y-m-d'),
                            'recorder_id' => $faker->randomElement($kaders),
                        ];

                        if ($isBalita) {
                            $umurBulan = $member->birth_date->diffInMonths($scheduleDate);
                            $data['weight'] = round(3.5 + ($umurBulan * 0.2) + $faker->randomFloat(1, -0.5, 1.5), 1);
                            $data['height'] = round(50 + ($umurBulan * 1.5) + $faker->randomFloat(1, -2, 3), 1);
                            $data['head_circumference'] = round($faker->randomFloat(1, 35, 45), 1);
                            $data['lila'] = round($faker->randomFloat(1, 10, 16), 1);
                            $status = $faker->randomElement(['Gizi Baik', 'Gizi Baik', 'Gizi Kurang', 'Gizi Lebih / Obesitas']);
                            $data['status_gizi'] = $status;
                            $data['z_score'] = ($status == 'Gizi Kurang') ? -2.5 : (($status == 'Gizi Baik') ? 0.5 : 2.5);
                        } else {
                            // Untuk Remaja, Dewasa, dan Lansia (Pengukuran ILP PTM)
                            $data['weight'] = round($faker->randomFloat(1, 40, 85), 1);
                            $data['height'] = round($faker->randomFloat(1, 145, 175), 1);
                            $data['belly_circumference'] = round($faker->randomFloat(1, 60, 100), 1);
                            $data['blood_pressure'] = rand(100, 150) . '/' . rand(70, 90);
                            $data['blood_sugar'] = rand(80, 200);
                            $data['uric_acid'] = round($faker->randomFloat(1, 3, 8), 1);
                            $data['cholesterol'] = rand(150, 260);
                            $data['examination_notes'] = $faker->randomElement(['Normal', 'Perlu pantauan pola makan', 'Rujuk faskes', 'Sehat walafiat']);
                        }

                        KmsRecord::create($data);
                    }
                }
            }
        }

        $this->command->info('Generate dummy data (50 Orang Warga mix usia & KMS ILP Lengkap) berhasil!');
    }
}
