<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id');

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping WorkspaceSeeder.');
            return;
        }

        $now = now();

        $workspaceData = [
            [
                'name' => 'Design Studio',
                'owner_id' => $users->first(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marketing Team',
                'owner_id' => $users->first(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Development Hub',
                'owner_id' => $users->count() > 1 ? $users[1] : $users->first(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('workspaces')->insert($workspaceData);
    }
}