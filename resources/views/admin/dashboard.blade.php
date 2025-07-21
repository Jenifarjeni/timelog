@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
            </h1>
            <div>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-success me-2">
                    <i class="fas fa-users me-1"></i>Manage Users
                </a>
                <a href="{{ route('timelog.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Time Logs
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Logged Dates</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_logged_dates'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Hours Logged</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_hours_logged'] }}h
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Tasks Logged</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_tasks_logged'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Average Hours/Day</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['average_hours_per_day'] }}h
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Filters -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filters
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Filter by User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $userFilter == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Filter by Date</label>
                        <select name="date" id="date" class="form-select">
                            <option value="">All Dates</option>
                            @foreach ($dates as $date)
                                <option value="{{ $date }}" {{ $dateFilter == $date ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="hours" class="form-label">Filter by Hours</label>
                        <select name="hours" id="hours" class="form-select">
                            <option value="">All Records</option>
                            <option value="exceeded" {{ $hoursFilter == 'exceeded' ? 'selected' : '' }}>
                                Days Exceeded 10 Hours
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Time Logs Table -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-table me-2"></i>Time Logs
                </h6>
            </div>
            <div class="card-body">
                @if ($timeLogs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Task Description</th>
                                    <th>Time Spent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($timeLogs as $log)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($log->work_date)->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $log->user->name }}</span>
                                        </td>
                                        <td>{{ Str::limit($log->task_description, 50) }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $log->hours }}h {{ $log->minutes }}m
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.timelog.edit', $log->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteTimeLog({{ $log->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $timeLogs->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                        <h5 class="text-gray-500">No time logs found</h5>
                        <p class="text-gray-400">No time logs match the current filters.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Grouped View -->
        @if ($groupedLogs->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt me-2"></i>Grouped by Date
                    </h6>
                </div>
                <div class="card-body">
                    @foreach ($groupedLogs as $date => $logs)
                        <div class="mb-4">
                            <h6 class="text-primary">
                                <i class="fas fa-calendar me-2"></i>
                                {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}
                            </h6>
                            <div class="list-group">
                                @foreach ($logs as $log)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $log->user->name }}</strong>:
                                            {{ Str::limit($log->task_description, 60) }}
                                        </div>
                                        <div>
                                            <span class="badge bg-info me-2">
                                                {{ $log->hours }}h {{ $log->minutes }}m
                                            </span>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.timelog.edit', $log->id) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="deleteTimeLog({{ $log->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this time log? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function deleteTimeLog(id) {
            if (confirm('Are you sure you want to delete this time log? This action cannot be undone.')) {
                fetch(`/admin/timelog/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        } else {
                            return response.json().then(data => {
                                throw new Error(data.error || 'Error deleting time log');
                            });
                        }
                    })
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                        }
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert(error.message || 'Error deleting time log');
                    });
            }
        }

        // Auto-submit form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelects = document.querySelectorAll('#user_id, #date, #hours');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });
        });
    </script>
@endpush
