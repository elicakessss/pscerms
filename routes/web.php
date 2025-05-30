<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Student\AccountController as StudentAccountController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Adviser\AccountController as AdviserAccountController;
use App\Http\Controllers\Adviser\DashboardController as AdviserDashboardController;
use App\Http\Controllers\Adviser\StudentManagementController as AdviserStudentManagementController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Home page - redirect to login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Logout route (generic)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Student Routes
Route::prefix('student')->name('student.')->group(function () {
    // Authenticated student routes
    Route::middleware('auth:student')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        // Account management
        Route::get('/account', [StudentAccountController::class, 'index'])->name('account.index');
        Route::get('/account/edit', [StudentAccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [StudentAccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [StudentAccountController::class, 'updatePassword'])->name('account.update_password');

        // Logout
        Route::post('/logout', function() {
            Auth::guard('student')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login');
        })->name('logout');
    });
});

// Adviser Routes
Route::prefix('adviser')->name('adviser.')->group(function () {
    // Authenticated adviser routes
    Route::middleware('auth:adviser')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdviserDashboardController::class, 'index'])->name('dashboard');

        // Account management
        Route::get('/account', [AdviserAccountController::class, 'index'])->name('account.index');
        Route::get('/account/edit', [AdviserAccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AdviserAccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [AdviserAccountController::class, 'updatePassword'])->name('account.update_password');

        // Student management
        Route::get('/student_management', [AdviserStudentManagementController::class, 'index'])->name('student_management.index');
        Route::get('/student_management/create', [AdviserStudentManagementController::class, 'create'])->name('student_management.create');
        Route::post('/student_management', [AdviserStudentManagementController::class, 'store'])->name('student_management.store');
        Route::get('/student_management/{student}', [AdviserStudentManagementController::class, 'show'])->name('student_management.show');
        Route::get('/student_management/{student}/edit', [AdviserStudentManagementController::class, 'edit'])->name('student_management.edit');
        Route::put('/student_management/{student}', [AdviserStudentManagementController::class, 'update'])->name('student_management.update');
        Route::delete('/student_management/{student}', [AdviserStudentManagementController::class, 'destroy'])->name('student_management.destroy');

        // Logout
        Route::post('/logout', function() {
            Auth::guard('adviser')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login');
        })->name('logout');
    });
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authenticated admin routes
    Route::middleware('auth:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User management - Custom routes to handle type parameter
        Route::get('/user_management', [AdminUserManagementController::class, 'index'])->name('user_management.index');
        Route::get('/user_management/create', [AdminUserManagementController::class, 'create'])->name('user_management.create');
        Route::post('/user_management', [AdminUserManagementController::class, 'store'])->name('user_management.store');
        Route::get('/user_management/{type}/{id}', [AdminUserManagementController::class, 'show'])->name('user_management.show');
        Route::get('/user_management/{type}/{id}/edit', [AdminUserManagementController::class, 'edit'])->name('user_management.edit');
        Route::put('/user_management/{type}/{id}', [AdminUserManagementController::class, 'update'])->name('user_management.update');
        Route::delete('/user_management/{type}/{id}', [AdminUserManagementController::class, 'destroy'])->name('user_management.destroy');

        // Logout
        Route::post('/logout', function() {
            Auth::guard('admin')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login');
        })->name('logout');
    });
});