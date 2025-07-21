@extends('layouts.app')

@section('title', 'Edit User - Admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-edit me-2"></i>Edit User (Admin)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Editing user:</strong> {{ $user->name }} ({{ $user->email }})
                        </div>

                        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin"
                                        value="1" {{ $user->is_admin ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_admin">
                                        <strong>Admin Privileges</strong>
                                    </label>
                                    <div class="form-text">
                                        Check this box to grant admin privileges to this user.
                                        Admins can access the admin dashboard and manage all users and time logs.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Users
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- User Statistics Card -->
                <div class="card shadow mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>User Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $user->getTotalTasksLogged() }}</h4>
                                    <p class="text-muted">Total Tasks Logged</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-success">{{ number_format($user->getTotalHoursLogged(), 1) }}h</h4>
                                    <p class="text-muted">Total Hours Logged</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h4 class="text-info">{{ $user->getUniqueDatesLogged() }}</h4>
                                    <p class="text-muted">Days with Logs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Information Card -->
                <div class="card shadow mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Account Type:</strong>
                                    @if ($user->isAdmin())
                                        <span class="badge bg-warning text-dark">Admin</span>
                                    @else
                                        <span class="badge bg-primary">Regular User</span>
                                    @endif
                                </p>
                                <p><strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</p>
                                <p><strong>Account ID:</strong> {{ $user->id }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone Card -->
                @if ($user->id !== auth()->id())
                    <div class="card shadow mt-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Once you delete a user, all of their resources and data will be permanently deleted.
                                Before deleting a user, please ensure you have backed up any important data.
                            </p>
                            <button type="button" class="btn btn-danger"
                                onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->name }}')">
                                <i class="fas fa-trash me-1"></i>Delete User
                            </button>
                        </div>
                    </div>
                @else
                    <div class="card shadow mt-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>Account Protection
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                You cannot delete your own account from this interface.
                                This prevents accidental self-deletion.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm User Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="userName"></strong>? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will permanently delete the user and all their time logs.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function confirmDeleteUser(userId, userName) {
            document.getElementById('userName').textContent = userName;
            document.getElementById('deleteUserForm').action = `/admin/users/${userId}`;

            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        }

        // Show success messages
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    </script>
@endpush
