@extends('layouts.app')

@section('title', 'Edit Time Log - Admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Edit Time Log (Admin)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Editing log for:</strong> {{ $timeLog->user->name }}
                            ({{ \Carbon\Carbon::parse($timeLog->work_date)->format('M d, Y') }})
                        </div>

                        <form method="POST" action="{{ route('admin.timelog.update', $timeLog->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="work_date" class="form-label">Date</label>
                                        <input type="date" class="form-control @error('work_date') is-invalid @enderror"
                                            id="work_date" name="work_date"
                                            value="{{ old('work_date', $timeLog->work_date) }}" max="{{ date('Y-m-d') }}"
                                            required>
                                        @error('work_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">User</label>
                                        <input type="text" class="form-control" value="{{ $timeLog->user->name }}"
                                            readonly>
                                        <small class="text-muted">User cannot be changed</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="task_description" class="form-label">Task Description</label>
                                <textarea class="form-control @error('task_description') is-invalid @enderror" id="task_description"
                                    name="task_description" rows="3" maxlength="1000" required>{{ old('task_description', $timeLog->task_description) }}</textarea>
                                <div class="form-text">
                                    <span id="char-count">0</span>/1000 characters
                                </div>
                                @error('task_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hours" class="form-label">Hours</label>
                                        <input type="number" class="form-control @error('hours') is-invalid @enderror"
                                            id="hours" name="hours" min="0" max="10"
                                            value="{{ old('hours', $timeLog->hours) }}" required>
                                        @error('hours')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="minutes" class="form-label">Minutes</label>
                                        <input type="number" class="form-control @error('minutes') is-invalid @enderror"
                                            id="minutes" name="minutes" min="0" max="59"
                                            value="{{ old('minutes', $timeLog->minutes) }}" required>
                                        @error('minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning" id="time-warning" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span id="warning-message"></span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Update Time Log
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const taskDescription = document.getElementById('task_description');
            const charCount = document.getElementById('char-count');
            const hoursInput = document.getElementById('hours');
            const minutesInput = document.getElementById('minutes');
            const timeWarning = document.getElementById('time-warning');
            const warningMessage = document.getElementById('warning-message');

            // Character counter
            function updateCharCount() {
                charCount.textContent = taskDescription.value.length;
            }

            taskDescription.addEventListener('input', updateCharCount);
            updateCharCount();

            // Time validation
            function validateTime() {
                const hours = parseInt(hoursInput.value) || 0;
                const minutes = parseInt(minutesInput.value) || 0;
                const totalMinutes = (hours * 60) + minutes;

                timeWarning.style.display = 'none';

                if (totalMinutes > 600) {
                    timeWarning.style.display = 'block';
                    warningMessage.textContent = 'A single task cannot exceed 10 hours (600 minutes).';
                    return false;
                }

                if (hours > 10) {
                    timeWarning.style.display = 'block';
                    warningMessage.textContent = 'Hours cannot exceed 10.';
                    return false;
                }

                if (minutes > 59) {
                    timeWarning.style.display = 'block';
                    warningMessage.textContent = 'Minutes cannot exceed 59.';
                    return false;
                }

                return true;
            }

            hoursInput.addEventListener('input', validateTime);
            minutesInput.addEventListener('input', validateTime);

            // Form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validateTime()) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endpush
