<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('users')->updateOrInsert(
            ['email' => 'raaz@test.com'],
            [
                'name' => 'Raaz',
                'password' => Hash::make('123456')
            ]
        );
        DB::table('users')->updateOrInsert(
            ['email' => 'mili@test.com'],
            [
                'name' => 'mili',
                'password' => Hash::make('123456')
            ]
        );
    }
}
