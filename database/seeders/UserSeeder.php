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
            ['email' => 'riturajchoudhary@test.com'],
            [
                'name' => 'rraj',
                'password' => Hash::make('123456')
            ]
        );
         DB::table('users')->updateOrInsert(
            ['email' => 'hv@test.com'],
            [
                'name' => 'hv',
                'password' => Hash::make('123456')
            ]
        );
    }
}
