<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $workspaces = DB::table('workspaces')->pluck('id');
        $users = DB::table('users')->pluck('id');

        if ($workspaces->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No workspaces or users found. Skipping ProjectSeeder.');
            return;
        }

        $now = now();

        $projectData = [
            [
                'name' => 'Brand Identity Redesign',
                'workspace_id' => $workspaces->first(),
                'client_name' => 'Acme Corp',
                'description' => 'Complete brand refresh including logo, colors, and typography',
                'status' => 'active',
                'deadline' => $now->copy()->addMonths(2),
                'created_by' => $users->first(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Website Mockups',
                'workspace_id' => $workspaces->first(),
                'client_name' => 'TechStart Inc',
                'description' => 'Homepage and landing page designs',
                'status' => 'active',
                'deadline' => $now->copy()->addWeeks(3),
                'created_by' => $users->first(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Q1 Campaign Assets',
                'workspace_id' => $workspaces->count() > 1 ? $workspaces[1] : $workspaces->first(),
                'client_name' => 'Global Marketing',
                'description' => 'Social media graphics and banner ads',
                'status' => 'active',
                'deadline' => $now->copy()->addWeeks(4),
                'created_by' => $users->count() > 1 ? $users[1] : $users->first(),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Mobile App UI',
                'workspace_id' => $workspaces->count() > 2 ? $workspaces[2] : $workspaces->first(),
                'client_name' => 'AppVenture',
                'description' => 'iOS and Android app interface design',
                'status' => 'archived',
                'deadline' => $now->copy()->subMonths(1),
                'created_by' => $users->count() > 1 ? $users[1] : $users->first(),
                'created_at' => $now->copy()->subMonths(3),
                'updated_at' => $now,
            ],
        ];

        DB::table('projects')->insert($projectData);
    }
}