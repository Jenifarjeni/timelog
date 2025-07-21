<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Time Log System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }

        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .btn-custom {
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 50px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-clock me-2"></i>Time Log System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('timelog.index') }}">
                                <i class="fas fa-list me-1"></i>All Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('timelog.create') }}">
                                <i class="fas fa-plus me-1"></i>Add Log
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                                    </a></li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-4">
                <i class="fas fa-clock me-3"></i>Time Log System
            </h1>
            <p class="lead mb-5">
                Track your daily work tasks efficiently with our comprehensive time logging solution.
                Monitor your productivity and stay within your daily work limits.
            </p>
            @auth
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('timelog.create') }}" class="btn btn-light btn-custom">
                        <i class="fas fa-plus me-2"></i>Add New Log
                    </a>
                    <a href="{{ route('timelog.index') }}" class="btn btn-outline-light btn-custom">
                        <i class="fas fa-list me-2"></i>View All Logs
                    </a>
                </div>
            @else
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('login') }}" class="btn btn-light btn-custom">
                        <i class="fas fa-sign-in-alt me-2"></i>Get Started
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-custom">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
            @endauth
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="h3 mb-4">Key Features</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center">
                        <div class="card-body">
                            <div class="feature-icon text-primary">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h5 class="card-title">Date Selection</h5>
                            <p class="card-text">
                                Choose your work date with our intuitive date picker.
                                Future dates are automatically prevented to ensure accurate logging.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center">
                        <div class="card-body">
                            <div class="feature-icon text-success">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h5 class="card-title">Time Tracking</h5>
                            <p class="card-text">
                                Log hours and minutes for each task.
                                Individual tasks are limited to 10 hours with daily total validation.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center">
                        <div class="card-body">
                            <div class="feature-icon text-info">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h5 class="card-title">Task Management</h5>
                            <p class="card-text">
                                Add detailed descriptions for each task.
                                Edit and manage all your time logs with full CRUD operations.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="mb-4">Daily Work Limits</h3>
                    <p class="lead mb-4">
                        Our system enforces a 10-hour daily work limit to help you maintain a healthy work-life balance.
                    </p>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Individual task time validation
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Daily total hour tracking
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Real-time progress indicators
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Visual progress bars
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-bar me-2"></i>Daily Summary Example
                            </h5>
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <h6 class="text-muted">Total Hours</h6>
                                    <h4 class="text-primary">7.5</h4>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-muted">Remaining</h6>
                                    <h4 class="text-success">2.5</h4>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-muted">Progress</h6>
                                    <h4 class="text-info">75%</h4>
                                </div>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-clock me-2"></i>
                Time Log System - Track your productivity efficiently
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</body>

</html>
