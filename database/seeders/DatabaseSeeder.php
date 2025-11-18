<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }*/

    public function run(): void
    {
        // Insert one admin user
        DB::table('admin')->insert([
            'admin_name' => 'Admin User',
            'admin_user' => 'admin',
            'admin_password' => Hash::make('12345'), // hashed password
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the inserted admin ID
        $adminId = DB::getPdo()->lastInsertId();

        // Insert one staff linked to the admin
        DB::table('staff')->insert([
            'admin_id' => $adminId,
            'staff_name' => 'Staff User',
            'staff_user' => 'staff',
            'staff_password' => Hash::make('12345'), // hashed password
            'staff_dob' => '1990-01-10',
            'staff_gender' => 'M',
            'staff_email' => 'staff@example.com',
            'staff_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
