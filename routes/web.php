<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/up', function () {
    return ['Laravel' => app()->version()];
})->name('up');

require __DIR__.'/auth.php';

// Time Log Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::resource('timelog', TimeLogController::class);
    Route::get('/timelog/date/{date}', [TimeLogController::class, 'getByDate'])->name('timelog.by-date');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes (require authentication and admin privileges)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management Routes
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
    
    // Time Log Management Routes
    Route::get('/timelog/{id}/edit', [AdminController::class, 'editTimeLog'])->name('timelog.edit');
    Route::put('/timelog/{id}', [AdminController::class, 'updateTimeLog'])->name('timelog.update');
    Route::delete('/timelog/{id}', [AdminController::class, 'deleteTimeLog'])->name('timelog.delete');
    Route::get('/timelog/date/{date}', [AdminController::class, 'getByDate'])->name('timelog.by-date');
});
