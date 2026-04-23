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
        // 1. Create teams used by the seeded team accounts
        $fsTeam = Team::firstOrCreate([
            'name' => 'FS Team',
        ]);

        $rpwsisTeam = Team::firstOrCreate([
            'name' => 'RPWSIS Team',
        ]);

        $cmTeam = Team::firstOrCreate([
            'name' => 'Contract Management Team',
        ]);

        $rowTeam = Team::firstOrCreate([
            'name' => 'ROW Team',
        ]);

        $pcrTeam = Team::firstOrCreate([
            'name' => 'PCR Team',
        ]);

        $paoTeam = Team::firstOrCreate([
            'name' => 'PAO Team',
        ]);

        // 2. Create the Admin User
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'agreed_to_terms' => true,
                'is_active' => true,
            ]
        );

        // 3. Create a Guest User
        User::updateOrCreate(
            ['email' => 'guest@test.com'],
            [
                'name' => 'Guest User',
                'password' => Hash::make('password'),
                'role' => 'guest',
                'email_verified_at' => now(),
                'agreed_to_terms' => true,
                'is_active' => true,
            ]
        );

        // 4. Create one seeded user per team role
        $teamUsers = [
            [
                'name' => 'FS Member',
                'email' => 'fsteam@test.com',
                'role' => 'fs_team',
                'team_id' => $fsTeam->id,
                'agreed_to_terms' => false,
            ],
            [
                'name' => 'RPWSIS Member',
                'email' => 'rpwsis@test.com',
                'role' => 'rpwsis_team',
                'team_id' => $rpwsisTeam->id,
                'agreed_to_terms' => true,
            ],
            [
                'name' => 'CM Member',
                'email' => 'cm@test.com',
                'role' => 'cm_team',
                'team_id' => $cmTeam->id,
                'agreed_to_terms' => true,
            ],
            [
                'name' => 'ROW Member',
                'email' => 'row@test.com',
                'role' => 'row_team',
                'team_id' => $rowTeam->id,
                'agreed_to_terms' => true,
            ],
            [
                'name' => 'PCR Member',
                'email' => 'pcr@test.com',
                'role' => 'pcr_team',
                'team_id' => $pcrTeam->id,
                'agreed_to_terms' => true,
            ],
            [
                'name' => 'PAO Member',
                'email' => 'pao@test.com',
                'role' => 'pao_team',
                'team_id' => $paoTeam->id,
                'agreed_to_terms' => true,
            ],
        ];

        foreach ($teamUsers as $teamUser) {
            User::updateOrCreate(
                ['email' => $teamUser['email']],
                [
                    'name' => $teamUser['name'],
                    'password' => Hash::make('password'),
                    'role' => $teamUser['role'],
                    'team_id' => $teamUser['team_id'],
                    'email_verified_at' => now(),
                    'agreed_to_terms' => $teamUser['agreed_to_terms'],
                    'is_active' => true,
                ]
            );
        }

        // 5. Create sample projects for the FS Team
        Project::firstOrCreate([
            'title' => 'IA RESOLUTIONS',
            'team_id' => $fsTeam->id,
        ], [
            'status' => 'Pending',
        ]);

        Project::firstOrCreate([
            'title' => 'System Upgrade Review',
            'team_id' => $fsTeam->id,
        ], [
            'status' => 'In Progress',
        ]);

        // 6. Seed per-team table data from the dedicated CSV seeders
        $this->call([
            HydroGeoProjectSeeder::class,
            FsdeProjectSeeder::class,
            ProcurementProjectSeeder::class,
            PcrStatusReportSeeder::class,
            PaoPowDataSeeder::class,
        ]);
    }
}
