@extends('layouts.app')

@section('title', 'Add Time Log')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus me-2"></i>Add New Time Log
                    </h4>
                </div>
                <div class="card-body">
                    <form id="timeLogForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="work_date" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Work Date *
                                    </label>
                                    <input type="text" class="form-control @error('work_date') is-invalid @enderror"
                                        id="work_date" name="work_date" value="{{ old('work_date', date('Y-m-d')) }}"
                                        required>
                                    <div class="invalid-feedback" id="work_date_error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-1"></i>Time Spent *
                                    </label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="number"
                                                class="form-control time-input @error('hours') is-invalid @enderror"
                                                id="hours" name="hours" placeholder="Hours" min="0"
                                                max="10" value="{{ old('hours', 0) }}" required>
                                            <div class="invalid-feedback" id="hours_error"></div>
                                        </div>
                                        <div class="col-6">
                                            <input type="number"
                                                class="form-control time-input @error('minutes') is-invalid @enderror"
                                                id="minutes" name="minutes" placeholder="Minutes" min="0"
                                                max="59" value="{{ old('minutes', 0) }}" required>
                                            <div class="invalid-feedback" id="minutes_error"></div>
                                        </div>
                                    </div>
                                    <!-- Time validation error display -->
                                    <div class="invalid-feedback d-block" id="time_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="task_description" class="form-label">
                                <i class="fas fa-tasks me-1"></i>Task Description *
                            </label>
                            <textarea class="form-control task-description @error('task_description') is-invalid @enderror" id="task_description"
                                name="task_description" rows="4" placeholder="Describe the task you worked on..." required>{{ old('task_description') }}</textarea>
                            <div class="invalid-feedback" id="task_description_error"></div>
                        </div>

                        <div class="daily-total" id="dailyTotal" style="display: none;">
                            <h6 class="mb-2">
                                <i class="fas fa-chart-bar me-1"></i>Daily Summary
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">Current Total: <span id="currentTotal">0</span> hours</small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Remaining: <span id="remainingTime">10</span> hours</small>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('timelog.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Time Log
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Recent logs for the selected date -->
            <div class="card mt-4" id="recentLogs" style="display: none;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Recent Logs for <span id="selectedDate"></span>
                    </h5>
                </div>
                <div class="card-body" id="recentLogsContent">
                    <!-- Recent logs will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize date picker
            const datePicker = flatpickr("#work_date", {
                dateFormat: "Y-m-d",
                maxDate: "today",
                defaultDate: "today",
                onChange: function(selectedDates, dateStr) {
                    loadDailyLogs(dateStr);
                }
            });

            // Load daily logs on page load
            loadDailyLogs($('#work_date').val());

            // Handle form submission
            $('#timeLogForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#time_error').hide();

                $.ajax({
                    url: '{{ route('timelog.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = '{{ route('timelog.index') }}';
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(key) {
                                if (key === 'time') {
                                    // Show time validation error
                                    $('#time_error').text(errors[key][0]).show();
                                    $('#hours, #minutes').addClass('is-invalid');
                                } else {
                                    // Show field-specific errors
                                    const field = $('#' + key);
                                    field.addClass('is-invalid');
                                    $('#' + key + '_error').text(errors[key][0]);
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while saving the time log.'
                            });
                        }
                    }
                });
            });

            // Load daily logs for a specific date
            function loadDailyLogs(date) {
                $.ajax({
                    url: `/timelog/date/${date}`,
                    type: 'GET',
                    success: function(response) {
                        updateDailyTotal(response.dailyTotal, response.dailyTotalMinutes);
                        updateRecentLogs(date, response.timeLogs);
                    },
                    error: function() {
                        console.log('Failed to load daily logs');
                    }
                });
            }

            // Update daily total display
            function updateDailyTotal(totalHours, totalMinutes) {
                const remainingHours = Math.max(0, 10 - totalHours);
                const progressPercentage = Math.min(100, (totalMinutes / 600) * 100);

                $('#currentTotal').text(totalHours.toFixed(2));
                $('#remainingTime').text(remainingHours.toFixed(2));
                $('#progressBar').css('width', progressPercentage + '%');

                if (totalMinutes > 0) {
                    $('#dailyTotal').show();
                } else {
                    $('#dailyTotal').hide();
                }

                // Update progress bar color
                if (progressPercentage >= 100) {
                    $('#progressBar').removeClass('bg-success bg-warning').addClass('bg-danger');
                } else if (progressPercentage >= 80) {
                    $('#progressBar').removeClass('bg-success bg-danger').addClass('bg-warning');
                } else {
                    $('#progressBar').removeClass('bg-warning bg-danger').addClass('bg-success');
                }
            }

            // Update recent logs display
            function updateRecentLogs(date, logs) {
                $('#selectedDate').text(new Date(date).toLocaleDateString());

                if (logs.length > 0) {
                    let html = '';
                    logs.forEach(function(log) {
                        html += `
                    <div class="time-log-item">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">${log.task_description}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    ${log.hours.toString().padStart(2, '0')}:${log.minutes.toString().padStart(2, '0')} (${(log.total_minutes / 60).toFixed(2)} hours)
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="/timelog/${log.id}/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    `;
                    });
                    $('#recentLogsContent').html(html);
                    $('#recentLogs').show();
                } else {
                    $('#recentLogs').hide();
                }
            }
        });
    </script>
@endpush
