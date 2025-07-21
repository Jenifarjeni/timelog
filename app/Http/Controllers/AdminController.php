<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(Request $request)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Get filter parameters
        $userFilter = $request->get('user_id');
        $dateFilter = $request->get('date');
        $hoursFilter = $request->get('hours');

        // Build query for time logs
        $query = TimeLog::with('user')->orderBy('work_date', 'desc');

        // Apply filters
        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        if ($dateFilter) {
            $query->where('work_date', $dateFilter);
        }

        if ($hoursFilter) {
            if ($hoursFilter === 'exceeded') {
                $query->whereIn('work_date', function($subQuery) {
                    $subQuery->select('work_date')
                        ->from('time_logs')
                        ->groupBy('work_date')
                        ->havingRaw('SUM(total_minutes) > 600'); // 10 hours = 600 minutes
                });
            }
        }

        $timeLogs = $query->paginate(20);

        // Get statistics
        $stats = $this->getStatistics($userFilter);

        // Get users for filter dropdown
        $users = User::orderBy('name')->get();

        // Get dates for filter dropdown
        $dates = TimeLog::select('work_date')
            ->distinct()
            ->orderBy('work_date', 'desc')
            ->pluck('work_date');

        // Group time logs by date for display
        $groupedLogs = $timeLogs->groupBy('work_date');

        return view('admin.dashboard', compact(
            'timeLogs',
            'stats',
            'users',
            'dates',
            'groupedLogs',
            'userFilter',
            'dateFilter',
            'hoursFilter'
        ));
    }

    /**
     * Display user management page.
     */
    public function users()
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $users = User::withCount('timeLogs')
            ->withSum('timeLogs', 'total_minutes')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Show the form for editing a user.
     */
    public function editUser($id)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $user = User::findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function updateUser(Request $request, $id)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_admin' => 'boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->has('is_admin'),
        ]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function deleteUser($id)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $user = User::findOrFail($id);

        // Prevent admin from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        // Delete user's time logs first
        $user->timeLogs()->delete();
        
        // Delete the user
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    /**
     * Get dashboard statistics.
     */
    private function getStatistics($userFilter = null)
    {
        $query = TimeLog::query();

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        $stats = [
            'total_users' => User::count(),
            'total_logged_dates' => $query->distinct('work_date')->count('work_date'),
            'total_hours_logged' => round($query->sum('total_minutes') / 60, 2),
            'total_tasks_logged' => $query->count(),
            'average_hours_per_day' => 0,
            
        ];

        // Calculate average hours per day
        if ($stats['total_logged_dates'] > 0) {
            $stats['average_hours_per_day'] = round($stats['total_hours_logged'] / $stats['total_logged_dates'], 2);
        }


        return $stats;
    }

    /**
     * Edit a time log (admin can edit any log).
     */
    public function editTimeLog($id)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $timeLog = TimeLog::with('user')->findOrFail($id);
        return view('admin.edit-time-log', compact('timeLog'));
    }

    /**
     * Update a time log (admin can update any log).
     */
    public function updateTimeLog(Request $request, $id)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $timeLog = TimeLog::findOrFail($id);

        $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'task_description' => 'required|string|max:1000',
            'hours' => 'required|integer|min:0|max:10',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        $totalMinutes = ($request->hours * 60) + $request->minutes;

        // Check if this single task exceeds 10 hours
        if ($totalMinutes > 600) {
            return back()->withErrors(['time' => 'A single task cannot exceed 10 hours.']);
        }

        // Check if total daily time would exceed 10 hours (excluding current task)
        $dailyTotal = TimeLog::where('user_id', $timeLog->user_id)
            ->where('work_date', $request->work_date)
            ->where('id', '!=', $timeLog->id)
            ->sum('total_minutes');

        if (($dailyTotal + $totalMinutes) > 600) {
            return back()->withErrors(['time' => 'Total daily time cannot exceed 10 hours.']);
        }

        $timeLog->update([
            'work_date' => $request->work_date,
            'task_description' => $request->task_description,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
            'total_minutes' => $totalMinutes,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Time log updated successfully.');
    }

    /**
     * Delete a time log (admin can delete any log).
     */
    public function deleteTimeLog($id, Request $request)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied. Admin privileges required.'], 403);
            }
            abort(403, 'Access denied. Admin privileges required.');
        }

        try {
            $timeLog = TimeLog::findOrFail($id);
            $timeLog->delete();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Time log deleted successfully.']);
            }
            
            return redirect()->route('admin.dashboard')->with('success', 'Time log deleted successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to delete time log.'], 500);
            }
            
            return redirect()->route('admin.dashboard')->with('error', 'Failed to delete time log.');
        }
    }

    /**
     * Get time logs by date for AJAX requests.
     */
    public function getByDate(Request $request)
    {
        // Check if user is admin
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $date = $request->get('date');
        $userFilter = $request->get('user_id');

        $query = TimeLog::with('user')
            ->where('work_date', $date);

        if ($userFilter) {
            $query->where('user_id', $userFilter);
        }

        $logs = $query->get();
        $dailyTotal = $logs->sum('total_minutes');

        return response()->json([
            'logs' => $logs,
            'daily_total' => $dailyTotal,
            'daily_total_formatted' => floor($dailyTotal / 60) . 'h ' . ($dailyTotal % 60) . 'm'
        ]);
    }
} 