<?php

namespace Tests\Feature;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class TimeLogDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_own_time_log()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Create a time log
        $timeLog = TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Test task',
            'hours' => 2,
            'minutes' => 30,
            'total_minutes' => 150,
        ]);

        // Delete the time log via AJAX
        $response = $this->deleteJson("/timelog/{$timeLog->id}");

        // Should succeed
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Time log deleted successfully.']);

        // Verify the record is deleted
        $this->assertDatabaseMissing('time_logs', ['id' => $timeLog->id]);
    }

    public function test_user_cannot_delete_other_users_time_log()
    {
        $user1 = User::factory()->create(['is_admin' => false]);
        $user2 = User::factory()->create(['is_admin' => false]);

        // Login as user1
        $this->actingAs($user1);

        // Create a time log for user2
        $timeLog = TimeLog::create([
            'user_id' => $user2->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Test task',
            'hours' => 2,
            'minutes' => 30,
            'total_minutes' => 150,
        ]);

        // Try to delete the time log via AJAX
        $response = $this->deleteJson("/timelog/{$timeLog->id}");

        // Should fail with access denied
        $response->assertStatus(403);
        $response->assertJson(['error' => 'Access denied.']);

        // Verify the record still exists
        $this->assertDatabaseHas('time_logs', ['id' => $timeLog->id]);
    }

    public function test_admin_can_delete_any_time_log()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        // Login as admin
        $this->actingAs($admin);

        // Create a time log for regular user
        $timeLog = TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Test task',
            'hours' => 2,
            'minutes' => 30,
            'total_minutes' => 150,
        ]);

        // Delete the time log via admin route
        $response = $this->deleteJson("/admin/timelog/{$timeLog->id}");

        // Should succeed
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Time log deleted successfully.']);

        // Verify the record is deleted
        $this->assertDatabaseMissing('time_logs', ['id' => $timeLog->id]);
    }

    public function test_non_admin_cannot_access_admin_delete_route()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as regular user
        $this->actingAs($user);

        // Create a time log
        $timeLog = TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Test task',
            'hours' => 2,
            'minutes' => 30,
            'total_minutes' => 150,
        ]);

        // Try to delete via admin route
        $response = $this->deleteJson("/admin/timelog/{$timeLog->id}");

        // Should fail with access denied
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Access denied. Admin privileges required.']);

        // Verify the record still exists
        $this->assertDatabaseHas('time_logs', ['id' => $timeLog->id]);
    }

    public function test_delete_nonexistent_time_log_returns_404()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Try to delete a non-existent time log
        $response = $this->deleteJson("/timelog/999");

        // Should return 404
        $response->assertStatus(404);
    }
} 