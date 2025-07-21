@extends('layouts.app')

@section('title', 'View Time Log')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-eye me-2"></i>Time Log Details
                        </h4>
                        <div class="btn-group" role="group">
                            <a href="{{ route('timelog.edit', $timeLog->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <button type="button" class="btn btn-danger btn-sm delete-log" data-id="{{ $timeLog->id }}"
                                data-description="{{ $timeLog->task_description }}">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar me-1"></i>Work Date
                                </label>
                                <p class="form-control-plaintext">
                                    {{ $timeLog->work_date->format('l, F j, Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-clock me-1"></i>Time Spent
                                </label>
                                <p class="form-control-plaintext">
                                    {{ $timeLog->formatted_time }} ({{ $timeLog->total_hours }} hours)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-tasks me-1"></i>Task Description
                        </label>
                        <div class="form-control-plaintext task-description">
                            {{ $timeLog->task_description }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-user me-1"></i>Created By
                                </label>
                                <p class="form-control-plaintext">
                                    {{ $timeLog->user->name }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-plus me-1"></i>Created At
                                </label>
                                <p class="form-control-plaintext">
                                    {{ $timeLog->created_at->format('M j, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if ($timeLog->updated_at != $timeLog->created_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-calendar-check me-1"></i>Last Updated
                                    </label>
                                    <p class="form-control-plaintext">
                                        {{ $timeLog->updated_at->format('M j, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('timelog.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                        <a href="{{ route('timelog.edit', $timeLog->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>Edit Time Log
                        </a>
                    </div>
                </div>
            </div>

            <!-- Daily summary for this date -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Daily Summary for
                        {{ $timeLog->work_date->format('l, F j, Y') }}
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $dailyLogs = Auth::user()
                            ->timeLogs()
                            ->where('work_date', $timeLog->work_date)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        $dailyTotal = $dailyLogs->sum('total_minutes');
                        $dailyTotalHours = round($dailyTotal / 60, 2);
                        $remainingHours = max(0, 10 - $dailyTotalHours);
                        $progressPercentage = min(100, ($dailyTotal / 600) * 100);
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Total Hours</h6>
                                <h4 class="text-primary">{{ $dailyTotalHours }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Remaining</h6>
                                <h4 class="text-success">{{ $remainingHours }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Progress</h6>
                                <h4 class="text-info">{{ number_format($progressPercentage, 1) }}%</h4>
                            </div>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar 
                        @if ($progressPercentage >= 100) bg-danger
                        @elseif($progressPercentage >= 80) bg-warning
                        @else bg-success @endif"
                            role="progressbar" style="width: {{ $progressPercentage }}%">
                        </div>
                    </div>

                    @if ($dailyLogs->count() > 1)
                        <h6 class="mb-3">All Tasks for This Date:</h6>
                        @foreach ($dailyLogs as $log)
                            <div class="time-log-item {{ $log->id == $timeLog->id ? 'bg-light' : '' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">
                                            {{ $log->task_description }}
                                            @if ($log->id == $timeLog->id)
                                                <span class="badge bg-primary">Current</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $log->formatted_time }} ({{ $log->total_hours }} hours)
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        @if ($log->id != $timeLog->id)
                                            <a href="{{ route('timelog.show', $log->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Delete confirmation
            $('.delete-log').click(function() {
                const id = $(this).data('id');
                const description = $(this).data('description');

                Swal.fire({
                    title: 'Delete Time Log?',
                    text: `Are you sure you want to delete "${description}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/timelog/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    window.location.href =
                                        '{{ route('timelog.index') }}';
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('Delete error:', xhr.responseText);
                                let errorMessage = 'Failed to delete time log.';

                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }

                                Swal.fire(
                                    'Error!',
                                    errorMessage,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
