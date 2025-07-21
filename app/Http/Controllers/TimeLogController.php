<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $timeLogs = Auth::user()->timeLogs()
            ->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('work_date');

        return view('timelog.index', compact('timeLogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('timelog.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'task_description' => 'required|string|max:1000',
            'hours' => 'required|integer|min:0|max:10',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        $totalMinutes = ($request->hours * 60) + $request->minutes;

        // Check if this single task exceeds 10 hours
        if ($totalMinutes > 600) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['time' => ['A single task cannot exceed 10 hours.']]], 422);
            }
            return back()->withErrors(['time' => 'A single task cannot exceed 10 hours.'])->withInput();
        }

        // Check if total daily time would exceed 10 hours
        $dailyTotal = Auth::user()->timeLogs()
            ->whereDate('work_date', $request->work_date)
            ->sum('total_minutes');

        if (($dailyTotal + $totalMinutes) > 600) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['time' => ['Total daily time cannot exceed 10 hours.']]], 422);
            }
            return back()->withErrors(['time' => 'Total daily time cannot exceed 10 hours.'])->withInput();
        }

        Auth::user()->timeLogs()->create([
            'work_date' => $request->work_date,
            'task_description' => $request->task_description,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
            'total_minutes' => $totalMinutes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Time log added successfully.']);
        }

        return redirect()->route('timelog.index')->with('success', 'Time log added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $timeLog = TimeLog::with('user')->findOrFail($id);
        
        // Check if user can view this log
        if (!Auth::user()->isAdmin() && $timeLog->user_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $dailyLogs = TimeLog::where('user_id', $timeLog->user_id)
            ->where('work_date', $timeLog->work_date)
            ->orderBy('created_at')
            ->get();

        $dailyTotal = $dailyLogs->sum('total_minutes');

        return view('timelog.show', compact('timeLog', 'dailyLogs', 'dailyTotal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $timeLog = TimeLog::findOrFail($id);
        
        // Check if user can edit this log
        if (!Auth::user()->isAdmin() && $timeLog->user_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        return view('timelog.edit', compact('timeLog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $timeLog = TimeLog::findOrFail($id);
        
        // Check if user can update this log
        if (!Auth::user()->isAdmin() && $timeLog->user_id !== Auth::id()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'work_date' => 'required|date|before_or_equal:today',
            'task_description' => 'required|string|max:1000',
            'hours' => 'required|integer|min:0|max:10',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        $totalMinutes = ($request->hours * 60) + $request->minutes;

        // Check if this single task exceeds 10 hours
        if ($totalMinutes > 600) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['time' => ['A single task cannot exceed 10 hours.']]], 422);
            }
            return back()->withErrors(['time' => 'A single task cannot exceed 10 hours.'])->withInput();
        }

        // Check if total daily time would exceed 10 hours (excluding current task)
        $dailyTotal = TimeLog::where('user_id', $timeLog->user_id)
            ->whereDate('work_date', $request->work_date)
            ->where('id', '!=', $timeLog->id)
            ->sum('total_minutes');

        if (($dailyTotal + $totalMinutes) > 600) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['time' => ['Total daily time cannot exceed 10 hours.']]], 422);
            }
            return back()->withErrors(['time' => 'Total daily time cannot exceed 10 hours.'])->withInput();
        }

        $timeLog->update([
            'work_date' => $request->work_date,
            'task_description' => $request->task_description,
            'hours' => $request->hours,
            'minutes' => $request->minutes,
            'total_minutes' => $totalMinutes,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Time log updated successfully.']);
        }

        return redirect()->route('timelog.index')->with('success', 'Time log updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $timeLog = TimeLog::findOrFail($id);
        
        // Check if user can delete this log
        if (!Auth::user()->isAdmin() && $timeLog->user_id !== Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied.'], 403);
            }
            abort(403, 'Access denied.');
        }

        try {
            $timeLog->delete();
            
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Time log deleted successfully.']);
            }
            
            return redirect()->route('timelog.index')->with('success', 'Time log deleted successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to delete time log.'], 500);
            }
            
            return redirect()->route('timelog.index')->with('error', 'Failed to delete time log.');
        }
    }

    /**
     * Get time logs by date for AJAX requests.
     */
    public function getByDate(Request $request)
    {
        $date = $request->get('date');
        $logs = Auth::user()->timeLogs()
            ->where('work_date', $date)
            ->orderBy('created_at')
            ->get();

        $dailyTotal = $logs->sum('total_minutes');

        return response()->json([
            'logs' => $logs,
            'daily_total' => $dailyTotal,
            'daily_total_formatted' => floor($dailyTotal / 60) . 'h ' . ($dailyTotal % 60) . 'm'
        ]);
    }
}