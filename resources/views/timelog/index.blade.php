@extends('layouts.app')

@section('title', 'All Time Logs')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-clock me-2"></i>All Time Logs
                </h1>
                <a href="{{ route('timelog.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add New Log
                </a>
            </div>

            @if ($timeLogs->count() > 0)
                @foreach ($timeLogs as $date => $logs)
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar me-2"></i>
                                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                                </h5>
                                <span class="badge bg-primary">
                                    {{ $logs->sum('total_minutes') / 60 }} hours
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($logs as $log)
                                <div class="time-log-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">{{ $log->task_description }}</h6>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $log->formatted_time }} ({{ $log->total_hours }} hours)
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('timelog.edit', $log->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-log"
                                                    data-id="{{ $log->id }}"
                                                    data-description="{{ $log->task_description }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No time logs found</h4>
                    <p class="text-muted">Start logging your work time by adding your first entry.</p>
                    <a href="{{ route('timelog.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add First Log
                    </a>
                </div>
            @endif
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
                                    location.reload();
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
