<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'no_hp' => '08123456789',
            'kelas' => 'Admin',
            'alergi' => null,
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'Active',
        ]);
        
        User::create([
            'name' => 'Kasir Pembayaran',
            'username' => 'kasir',
            'no_hp' => '08123456789',
            'kelas' => 'kasir',
            'alergi' => null,
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'status' => 'Active',
        ]);

        // Customer users
        $customers = [
            [
                'name' => 'Fathullah',
                'username' => 'fathullah',
                'no_hp' => '08123456780',
                'kelas' => '12 B',
                'alergi' => null,
            ],
            [
                'name' => 'Ahmad',
                'username' => 'ahmad',
                'no_hp' => '08123456781',
                'kelas' => '11 A',
                'alergi' => 'Kacang',
            ],
            [
                'name' => 'Siti',
                'username' => 'siti',
                'no_hp' => '08123456782',
                'kelas' => '10 C',
                'alergi' => null,
            ],
            [
                'name' => 'Budi',
                'username' => 'budi',
                'no_hp' => '08123456783',
                'kelas' => '12 B',
                'alergi' => 'Seafood',
            ],
            [
                'name' => 'Rina',
                'username' => 'rina',
                'no_hp' => '08123456784',
                'kelas' => '11 A',
                'alergi' => null,
            ],
        ];

        foreach ($customers as $customer) {
            User::create([
                'name' => $customer['name'],
                'username' => $customer['username'],
                'no_hp' => $customer['no_hp'],
                'kelas' => $customer['kelas'],
                'alergi' => $customer['alergi'],
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'Active',
            ]);
        }
    }
}