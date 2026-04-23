<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the FS Team
        $fsTeam = Team::create([
            'name' => 'FS Team'
        ]);

        // 2. Create the Admin User
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'agreed_to_terms' => true, // Admin already agreed
        ]);

        // 3. Create a Guest User
        User::create([
            'name' => 'Guest User',
            'email' => 'guest@test.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
            'email_verified_at' => now(),
            'agreed_to_terms' => true,
        ]);

        // 4. Create an FS Team User (Set terms to false to test the RA10173 page)
        User::create([
            'name' => 'FS Member',
            'email' => 'fsteam@test.com',
            'password' => Hash::make('password'),
            'role' => 'fs_team',
            'team_id' => $fsTeam->id,
            'email_verified_at' => now(),
            'agreed_to_terms' => false,
        ]);

        // 5. Create sample projects for the FS Team
        Project::create([
            'title' => 'IA RESOLUTIONS',
            'status' => 'Pending',
            'team_id' => $fsTeam->id,
        ]);

        Project::create([
            'title' => 'System Upgrade Review',
            'status' => 'In Progress',
            'team_id' => $fsTeam->id,
        ]);
    }
}
