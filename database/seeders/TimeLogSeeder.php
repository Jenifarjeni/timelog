<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TimeLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TimeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // Create regular user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Sample time logs for admin
        $adminLogs = [
            ['2025-07-20', 'System Administration', 2, 30],
            ['2025-07-20', 'User Management', 1, 45],
            ['2025-07-19', 'Database Maintenance', 3, 15],
            ['2025-07-19', 'Security Updates', 2, 0],
            ['2025-07-18', 'Backup Configuration', 1, 30],
            ['2025-07-18', 'Performance Monitoring', 2, 45],
        ];

        foreach ($adminLogs as $log) {
            $totalMinutes = ($log[2] * 60) + $log[3];
            TimeLog::create([
                'user_id' => $admin->id,
                'work_date' => $log[0],
                'task_description' => $log[1],
                'hours' => $log[2],
                'minutes' => $log[3],
                'total_minutes' => $totalMinutes,
            ]);
        }

        // Sample time logs for regular user
        $userLogs = [
            ['2025-07-20', 'API Development', 3, 0],
            ['2025-07-20', 'Code Review', 1, 30],
            ['2025-07-19', 'Frontend Development', 4, 15],
            ['2025-07-19', 'Testing', 2, 45],
            ['2025-07-18', 'Bug Fixing', 3, 30],
            ['2025-07-18', 'Documentation', 1, 15],
            ['2025-07-17', 'Team Meeting', 1, 0],
            ['2025-07-17', 'Project Planning', 2, 30],
        ];

        foreach ($userLogs as $log) {
            $totalMinutes = ($log[2] * 60) + $log[3];
            TimeLog::create([
                'user_id' => $user->id,
                'work_date' => $log[0],
                'task_description' => $log[1],
                'hours' => $log[2],
                'minutes' => $log[3],
                'total_minutes' => $totalMinutes,
            ]);
        }
    }
}
