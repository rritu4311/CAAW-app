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
        DB::table('users')->insert([
            'name'=>'Raaz',
            'email'=>'raaz@test.com',
            'password'=>Hash::make('123456')
        ]);
        DB::table('users')->insert([
            'name'=>'mili',
            'email'=>'mili@test.com',
            'password'=>Hash::make('123456')
        ]);
    }
}
