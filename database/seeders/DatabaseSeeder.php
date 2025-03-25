<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'first_name' => 'BahariHost',
            'last_name' => 'Admin',
            'email' => 'baharihost@gmail.com',
            'role' => 'admin',
            'password' => Hash::make('Ba123456'),
            'phone' => '+8801611414133',
            'address' => 'Nobodoy, Bangladesh',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'profile_photo' => 'assets/img/avatars/default.png',
        ]);

        CompanySetting::create([
            'company_name' => 'BahariHost',
            'company_email' => 'support@baharihost.com',
            'company_phone' => '+8801726708442',
            'company_address' => 'Nobodoy, Bangladesh',
            'company_logo' => 'assets/img/company_logo/logo.png',
            'company_favicon' => 'assets/img/company_favicon/favicon.png',
            'company_website' => 'https://baharihost.com',
            'company_facebook' => 'https://facebook.com/baharihost',
        ]);

    }
}
