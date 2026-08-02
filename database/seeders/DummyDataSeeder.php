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

        $this->command->info('Mulai generate dummy data (2 Kader, 20 Dewasa, 10 Remaja, 10 Balita, Jadwal 3 bulan terakhir)...');

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

        // 2. Buat 20 Dewasa (Sebagai User & Kepala Keluarga)
        // Kita asumsikan 1 dewasa = 1 KK. 
        // 20 Dewasa ini akan menjadi 'warga'
        $families = [];
        for ($i = 1; $i <= 20; $i++) {
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

            // Masukkan dirinya sendiri sebagai Kepala Keluarga di family_members
            FamilyMember::create([
                'family_id' => $family->id,
                'nik' => $warga->nik,
                'name' => $warga->name,
                'birth_date' => Carbon::now()->subYears(rand(25, 65)),
                'gender' => $faker->randomElement(['L', 'P']),
                'relation' => 'kepala_keluarga',
            ]);
        }

        // 3. Buat 10 Remaja (Usia 12-18 th)
        for ($i = 1; $i <= 10; $i++) {
            FamilyMember::create([
                'family_id' => $faker->randomElement($families),
                'nik' => $faker->unique()->numerify('3275############'),
                'name' => 'Remaja ' . $faker->firstName,
                'birth_date' => Carbon::now()->subYears(rand(12, 18)),
                'gender' => $faker->randomElement(['L', 'P']),
                'relation' => 'anak',
            ]);
        }

        // 4. Buat 10 Balita (Usia 0-5 th) dan datanya di KMS
        $balitaIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $umurBulan = rand(3, 58); // Umur balita dalam bulan
            $birthDate = Carbon::now()->subMonths($umurBulan);

            $balita = FamilyMember::create([
                'family_id' => $faker->randomElement($families),
                'nik' => $faker->unique()->numerify('3275############'),
                'name' => 'Balita ' . $faker->firstName,
                'birth_date' => $birthDate,
                'gender' => $faker->randomElement(['L', 'P']),
                'relation' => 'anak',
            ]);
            $balitaIds[] = $balita;
        }

        // 5. Buat Jadwal 3 Bulan Terakhir (Bulan ini, bulan lalu, 2 bulan lalu)
        // Dan isi data penimbangan Balita pada jadwal Posyandu Balita tersebut
        $kategoriKegiatan = [
            ['title' => 'Posyandu Balita & Imunisasi', 'type' => 'balita'],
            ['title' => 'Posyandu Lansia Sehat', 'type' => 'lansia'],
            ['title' => 'Posbindu / Posyandu Remaja', 'type' => 'remaja'],
        ];

        for ($m = 0; $m <= 2; $m++) {
            $targetMonth = Carbon::now()->subMonths($m);

            foreach ($kategoriKegiatan as $keg) {
                // Tanggal jadwal di bulan tersebut (antara tgl 5 - 25)
                $scheduleDate = $targetMonth->copy()->startOfMonth()->addDays(rand(5, 25))->setHour(8)->setMinute(0);

                $schedule = Schedule::create([
                    'title' => $keg['title'] . ' - ' . $scheduleDate->translatedFormat('F Y'),
                    'description' => 'Kegiatan rutin ' . strtolower($keg['title']) . ' tingkat RW.',
                    'schedule_date' => $scheduleDate,
                    'location' => 'Balai Warga RW ' . str_pad(rand(1, 10), 3, '0', STR_PAD_LEFT),
                    'created_by' => $kaders[0],
                ]);

                // Jika kegiatannya Balita, kita isi data KMS Records untuk 10 balita tersebut
                if ($keg['type'] == 'balita') {
                    foreach ($balitaIds as $balita) {
                        // Pastikan balita sudah lahir pada saat jadwal ini
                        if ($scheduleDate->greaterThanOrEqualTo($balita->birth_date)) {
                            // Generate berat dan tinggi fiktif yang realistis
                            $umurSaatTimbang = $balita->birth_date->diffInMonths($scheduleDate);
                            $baseWeight = 3.5 + ($umurSaatTimbang * 0.2); // rumus kasaran
                            $baseHeight = 50 + ($umurSaatTimbang * 1.5);

                            $weight = round($baseWeight + ($faker->randomFloat(1, -0.5, 1.5)), 1);
                            $height = round($baseHeight + ($faker->randomFloat(1, -2, 3)), 1);

                            $statusGizi = $faker->randomElement(['Gizi Baik', 'Gizi Baik', 'Gizi Baik', 'Gizi Kurang', 'Gizi Lebih / Obesitas']);

                            KmsRecord::create([
                                'family_member_id' => $balita->id,
                                'recorded_date' => $scheduleDate->format('Y-m-d'),
                                'weight' => $weight,
                                'height' => $height,
                                'head_circumference' => round($faker->randomFloat(1, 35, 45), 1),
                                'lila' => round($faker->randomFloat(1, 10, 16), 1),
                                'z_score' => ($statusGizi == 'Gizi Kurang') ? -2.5 : (($statusGizi == 'Gizi Baik') ? 0.5 : 2.5),
                                'status_gizi' => $statusGizi,
                                'recorder_id' => $faker->randomElement($kaders),
                            ]);
                        }
                    }
                }
            }
        }



        $this->command->info('Generate dummy data berhasil!');
    }
}
