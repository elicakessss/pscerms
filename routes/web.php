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
use App\Http\Controllers\Admin\CouncilManagementController as AdminCouncilManagementController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Home page - redirect to login
Route::get('/', function () {
    return redirect()->route('login');})->name('home');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

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

        // My Councils management
        Route::get('/councils', [App\Http\Controllers\Student\CouncilController::class, 'index'])->name('councils.index');
        Route::get('/councils/{council}', [App\Http\Controllers\Student\CouncilController::class, 'show'])->name('councils.show');

        // Evaluation management
        Route::get('/evaluation/self/{council}', [App\Http\Controllers\Student\EvaluationController::class, 'showSelf'])->name('evaluation.self');
        Route::get('/evaluation/peer/{council}/{evaluatedStudent}', [App\Http\Controllers\Student\EvaluationController::class, 'showPeer'])->name('evaluation.peer');
        Route::post('/evaluation', [App\Http\Controllers\Student\EvaluationController::class, 'store'])->name('evaluation.store');

        // Leadership Certificate management
        Route::get('/leadership-certificate/create', [StudentDashboardController::class, 'createCertificateRequest'])->name('leadership_certificate.create');
        Route::post('/leadership-certificate', [StudentDashboardController::class, 'storeCertificateRequest'])->name('leadership_certificate.store');
        Route::get('/leadership-certificate', [StudentDashboardController::class, 'certificateRequests'])->name('leadership_certificate.index');

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

        // Council management
        Route::get('/councils', [App\Http\Controllers\Adviser\CouncilController::class, 'index'])->name('councils.index');
        Route::get('/councils/create', [App\Http\Controllers\Adviser\CouncilController::class, 'create'])->name('councils.create');
        Route::post('/councils', [App\Http\Controllers\Adviser\CouncilController::class, 'store'])->name('councils.store');
        Route::get('/councils/{council}', [App\Http\Controllers\Adviser\CouncilController::class, 'show'])->name('councils.show');
        Route::delete('/councils/{council}', [App\Http\Controllers\Adviser\CouncilController::class, 'destroy'])->name('councils.destroy');
        Route::post('/councils/{council}/officers', [App\Http\Controllers\Adviser\CouncilController::class, 'assignOfficer'])->name('councils.assign_officer');
        Route::post('/councils/{council}/coordinators', [App\Http\Controllers\Adviser\CouncilController::class, 'addCoordinator'])->name('councils.add_coordinator');
        Route::post('/councils/{council}/senators', [App\Http\Controllers\Adviser\CouncilController::class, 'addSenator'])->name('councils.add_senator');
        Route::post('/councils/{council}/congressmen', [App\Http\Controllers\Adviser\CouncilController::class, 'addCongressman'])->name('councils.add_congressman');
        Route::post('/councils/{council}/justices', [App\Http\Controllers\Adviser\CouncilController::class, 'addJustice'])->name('councils.add_justice');
        Route::put('/councils/{council}/officers/{officer}', [App\Http\Controllers\Adviser\CouncilController::class, 'updateOfficer'])->name('councils.update_officer');
        Route::delete('/councils/{council}/officers/{officer}', [App\Http\Controllers\Adviser\CouncilController::class, 'removeOfficer'])->name('councils.remove_officer');

        // Peer evaluator assignment
        Route::post('/councils/{council}/officers/{officer}/assign-peer-evaluator', [App\Http\Controllers\Adviser\CouncilController::class, 'assignPeerEvaluator'])->name('councils.assign_peer_evaluator');
        Route::delete('/councils/{council}/officers/{officer}/remove-peer-evaluator', [App\Http\Controllers\Adviser\CouncilController::class, 'removePeerEvaluator'])->name('councils.remove_peer_evaluator');

        // Evaluation management
        Route::get('/evaluation/{council}/{student}', [App\Http\Controllers\Adviser\EvaluationController::class, 'show'])->name('evaluation.show');
        Route::post('/evaluation', [App\Http\Controllers\Adviser\EvaluationController::class, 'store'])->name('evaluation.store');

        // Evaluation management
        Route::post('/councils/{council}/start-evaluations', [App\Http\Controllers\Adviser\CouncilController::class, 'startEvaluations'])->name('councils.start_evaluations');
        Route::delete('/councils/{council}/clear-evaluations', [App\Http\Controllers\Adviser\CouncilController::class, 'clearEvaluations'])->name('councils.clear_evaluations');

        // Leadership Certificate management
        Route::get('/leadership-certificate/{request}', [AdviserDashboardController::class, 'viewCertificateRequest'])->name('leadership_certificate.show');
        Route::post('/leadership-certificate/{request}/approve', [AdviserDashboardController::class, 'approveCertificateRequest'])->name('leadership_certificate.approve');
        Route::post('/leadership-certificate/{request}/dismiss', [AdviserDashboardController::class, 'dismissCertificateRequest'])->name('leadership_certificate.dismiss');

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

        // Account management
        Route::get('/account', [AdminAccountController::class, 'index'])->name('account.index');
        Route::get('/account/edit', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::put('/account', [AdminAccountController::class, 'update'])->name('account.update');
        Route::put('/account/password', [AdminAccountController::class, 'updatePassword'])->name('account.update_password');

        // User management - Custom routes to handle type parameter
        Route::get('/user_management', [AdminUserManagementController::class, 'index'])->name('user_management.index');
        Route::get('/user_management/create', [AdminUserManagementController::class, 'create'])->name('user_management.create');
        Route::post('/user_management', [AdminUserManagementController::class, 'store'])->name('user_management.store');
        Route::get('/user_management/{type}/{id}', [AdminUserManagementController::class, 'show'])->name('user_management.show');
        Route::get('/user_management/{type}/{id}/edit', [AdminUserManagementController::class, 'edit'])->name('user_management.edit');
        Route::put('/user_management/{type}/{id}', [AdminUserManagementController::class, 'update'])->name('user_management.update');
        Route::delete('/user_management/{type}/{id}', [AdminUserManagementController::class, 'destroy'])->name('user_management.destroy');

        // Department management
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);

        // Council management
        Route::resource('council_management', AdminCouncilManagementController::class)->parameters([
            'council_management' => 'council'
        ]);
        Route::put('/council_management/academic-year/update', [AdminCouncilManagementController::class, 'updateAcademicYear'])->name('council_management.update_academic_year');

        // Logout
        Route::post('/logout', function() {
            Auth::guard('admin')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login');
        })->name('logout');
    });
});
