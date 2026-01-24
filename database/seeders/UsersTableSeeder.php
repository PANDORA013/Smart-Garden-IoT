<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create Admin User if not exists
        // User::Create(
        //     ['email' => 'admin@gmail.com'],
        //     [
        //         'name' => 'Admin User',
        //         'password' => Hash::make('admin123'),
        //         'role' => 'admin',
        //     ]
        // );

        User::create([
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('admin123'),

            ]);

        // Create SDM User if not exists
        // User::updateOrCreate(
        //     ['email' => 'sdm@gmail.com'],
        //     [
        //         'name' => 'SDM User',
        //         'password' => Hash::make('admin123'),
        //         'role' => 'sdm',
        //     ]
        // );

        // // Create Marketing User if not exists
        // User::updateOrCreate(
        //     ['email' => 'marketing@gmail.com'],
        //     [
        //         'name' => 'Marketing User',
        //         'password' => Hash::make('admin123'),
        //         'role' => 'marketing',
        //     ]
        // );
    }
}
