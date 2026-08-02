<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['nik' => '1111111111111111'],
            [
                'name' => 'Super Admin',
                'role' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'phone' => '081111111111',
            ]
        );

        \App\Models\User::updateOrCreate(
            ['nik' => '2222222222222222'],
            [
                'name' => 'Kader Posyandu',
                'role' => 'kader',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'phone' => '082222222222',
            ]
        );

        \App\Models\User::updateOrCreate(
            ['nik' => '3333333333333333'],
            [
                'name' => 'Warga Masyarakat',
                'role' => 'warga',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'phone' => '083333333333',
            ]
        );

        $this->call([
            \Laravolt\Indonesia\Seeds\DatabaseSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
