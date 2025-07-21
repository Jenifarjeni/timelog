<?php

namespace Tests\Feature;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class TimeLogValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_exceed_10_hours_per_day()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Add 9 hours of work for today
        TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'First task',
            'hours' => 9,
            'minutes' => 0,
            'total_minutes' => 540,
        ]);

        // Try to add 2 more hours (which would exceed 10 hours)
        $response = $this->post('/timelog', [
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Second task',
            'hours' => 2,
            'minutes' => 0,
        ]);

        // Should get validation error
        $response->assertSessionHasErrors(['time']);
        $this->assertStringContainsString('Total daily time cannot exceed 10 hours', session('errors')->first('time'));
    }

    public function test_user_can_add_time_when_under_10_hours()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Add 8 hours of work for today
        TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'First task',
            'hours' => 8,
            'minutes' => 0,
            'total_minutes' => 480,
        ]);

        // Try to add 1 more hour (total would be 9 hours)
        $response = $this->post('/timelog', [
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Second task',
            'hours' => 1,
            'minutes' => 0,
        ]);

        // Should succeed
        $response->assertRedirect('/timelog');
        $response->assertSessionHas('success');
    }

    public function test_single_task_cannot_exceed_10_hours_via_total_minutes()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Try to add a task with 10 hours and 1 minute (601 minutes total)
        $response = $this->post('/timelog', [
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Long task',
            'hours' => 10,
            'minutes' => 1,
        ]);

        // Should get validation error for time
        $response->assertSessionHasErrors(['time']);
        $this->assertStringContainsString('A single task cannot exceed 10 hours', session('errors')->first('time'));
    }

    public function test_edit_validation_excludes_current_task()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Create a time log with 8 hours
        $timeLog = TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'First task',
            'hours' => 8,
            'minutes' => 0,
            'total_minutes' => 480,
        ]);

        // Add another 1 hour task
        TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Second task',
            'hours' => 1,
            'minutes' => 0,
            'total_minutes' => 60,
        ]);

        // Try to edit the first task to 2 hours (total would be 3 hours, which is fine)
        $response = $this->put("/timelog/{$timeLog->id}", [
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Updated task',
            'hours' => 2,
            'minutes' => 0,
        ]);

        // Should succeed
        $response->assertRedirect('/timelog');
        $response->assertSessionHas('success');
    }

    public function test_edit_validation_prevents_exceeding_limit_via_total_minutes()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Create a time log with 8 hours
        $timeLog = TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'First task',
            'hours' => 8,
            'minutes' => 0,
            'total_minutes' => 480,
        ]);

        // Add another 1 hour task
        TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Second task',
            'hours' => 1,
            'minutes' => 0,
            'total_minutes' => 60,
        ]);

        // Try to edit the first task to 10 hours and 1 minute (should fail for single task limit)
        $response = $this->put("/timelog/{$timeLog->id}", [
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Updated task',
            'hours' => 10,
            'minutes' => 1,
        ]);

        // Should get validation error for time
        $response->assertSessionHasErrors(['time']);
        $this->assertStringContainsString('A single task cannot exceed 10 hours', session('errors')->first('time'));
    }

    public function test_debug_validation_response()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        // Add 9 hours of work for today
        TimeLog::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'First task',
            'hours' => 9,
            'minutes' => 0,
            'total_minutes' => 540,
        ]);

        // Try to add 2 more hours (which would exceed 10 hours)
        $response = $this->post('/timelog', [
            'work_date' => now()->format('Y-m-d'),
            'task_description' => 'Second task',
            'hours' => 2,
            'minutes' => 0,
        ]);

        // Debug: Check what response we're getting
        dump($response->getContent());
        dump($response->getStatusCode());
        dump(session()->all());
        
        // Should get validation error
        $response->assertSessionHasErrors(['time']);
        $this->assertStringContainsString('Total daily time cannot exceed 10 hours', session('errors')->first('time'));
    }

    public function test_verify_calculation_logic()
    {
        $user = User::factory()->create(['is_admin' => false]);

        // Login as user
        $this->actingAs($user);

        $today = now()->format('Y-m-d');
        dump("Today's date: {$today}");

        // Add 9 hours of work for today
        $timeLog = TimeLog::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'task_description' => 'First task',
            'hours' => 9,
            'minutes' => 0,
            'total_minutes' => 540,
        ]);

        dump("Created TimeLog ID: {$timeLog->id}");
        dump("TimeLog user_id: {$timeLog->user_id}");
        dump("TimeLog work_date: {$timeLog->work_date}");
        dump("TimeLog total_minutes: {$timeLog->total_minutes}");

        // Check what the daily total is
        $dailyTotal = Auth::user()->timeLogs()
            ->where('work_date', $today)
            ->sum('total_minutes');

        dump("Daily total: {$dailyTotal} minutes");
        dump("Expected: 540 minutes");
        
        // Also check all time logs for this user
        $allLogs = Auth::user()->timeLogs()->get();
        dump("All logs count: " . $allLogs->count());
        foreach ($allLogs as $log) {
            dump("Log ID: {$log->id}, Date: {$log->work_date}, Minutes: {$log->total_minutes}");
        }
        
        // Try a different query approach
        $dailyTotal2 = TimeLog::where('user_id', $user->id)
            ->where('work_date', $today)
            ->sum('total_minutes');
        dump("Daily total (alternative query): {$dailyTotal2} minutes");
        
        // Try to add 2 more hours
        $newTaskMinutes = 120; // 2 hours
        $totalWouldBe = $dailyTotal + $newTaskMinutes;
        
        dump("New task: {$newTaskMinutes} minutes");
        dump("Total would be: {$totalWouldBe} minutes");
        dump("Limit: 600 minutes");
        dump("Would exceed: " . ($totalWouldBe > 600 ? 'YES' : 'NO'));
        
        $this->assertEquals(540, $dailyTotal);
        $this->assertEquals(660, $totalWouldBe);
        $this->assertTrue($totalWouldBe > 600);
    }
} 