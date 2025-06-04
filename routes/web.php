<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Authentication Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Student Controllers
use App\Http\Controllers\Student\AccountController as StudentAccountController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\CouncilController as StudentCouncilController;
use App\Http\Controllers\Student\EvaluationController as StudentEvaluationController;

// Adviser Controllers
use App\Http\Controllers\Adviser\AccountController as AdviserAccountController;
use App\Http\Controllers\Adviser\DashboardController as AdviserDashboardController;
use App\Http\Controllers\Adviser\StudentManagementController as AdviserStudentManagementController;
use App\Http\Controllers\Adviser\CouncilController as AdviserCouncilController;
use App\Http\Controllers\Adviser\EvaluationController as AdviserEvaluationController;

// Admin Controllers
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController as AdminUserManagementController;
use App\Http\Controllers\Admin\CouncilManagementController as AdminCouncilManagementController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\EvaluationFormController as AdminEvaluationFormController;

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
        Route::get('/councils', [StudentCouncilController::class, 'index'])->name('councils.index');
        Route::get('/councils/{council}', [StudentCouncilController::class, 'show'])->name('councils.show');

        // Evaluation management
        Route::get('/evaluation/self/{council}', [StudentEvaluationController::class, 'showSelf'])->name('evaluation.self');
        Route::get('/evaluation/peer/{council}/{evaluatedStudent}', [StudentEvaluationController::class, 'showPeer'])->name('evaluation.peer');
        Route::post('/evaluation', [StudentEvaluationController::class, 'store'])->name('evaluation.student_store');

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
        Route::get('/councils', [AdviserCouncilController::class, 'index'])->name('councils.index');
        Route::get('/councils/create', [AdviserCouncilController::class, 'create'])->name('councils.create');
        Route::post('/councils', [AdviserCouncilController::class, 'store'])->name('councils.store');
        Route::get('/councils/{council}', [AdviserCouncilController::class, 'show'])->name('councils.show');
        Route::delete('/councils/{council}', [AdviserCouncilController::class, 'destroy'])->name('councils.destroy');
        Route::get('/councils/{council}/search-students', [AdviserCouncilController::class, 'searchStudents'])->name('councils.search_students');
        Route::post('/councils/{council}/officers', [AdviserCouncilController::class, 'assignOfficer'])->name('councils.assign_officer');
        Route::post('/councils/{council}/coordinators', [AdviserCouncilController::class, 'addCoordinator'])->name('councils.add_coordinator');
        Route::post('/councils/{council}/senators', [AdviserCouncilController::class, 'addSenator'])->name('councils.add_senator');
        Route::post('/councils/{council}/congressmen', [AdviserCouncilController::class, 'addCongressman'])->name('councils.add_congressman');
        Route::post('/councils/{council}/justices', [AdviserCouncilController::class, 'addJustice'])->name('councils.add_justice');
        Route::put('/councils/{council}/officers/{officer}', [AdviserCouncilController::class, 'updateOfficer'])->name('councils.update_officer');
        Route::delete('/councils/{council}/officers/{officer}', [AdviserCouncilController::class, 'removeOfficer'])->name('councils.remove_officer');

        // Peer evaluator assignment
        Route::post('/councils/{council}/officers/{officer}/assign-peer-evaluator', [AdviserCouncilController::class, 'assignPeerEvaluator'])->name('councils.assign_peer_evaluator');
        Route::delete('/councils/{council}/officers/{officer}/remove-peer-evaluator', [AdviserCouncilController::class, 'removePeerEvaluator'])->name('councils.remove_peer_evaluator');

        // Evaluation management
        Route::get('/evaluation/{council}/{student}', [AdviserEvaluationController::class, 'show'])->name('evaluation.show');
        Route::post('/evaluation', [AdviserEvaluationController::class, 'store'])->name('evaluation.adviser_store');

        // Evaluation instance management
        Route::post('/councils/{council}/start-evaluations', [AdviserCouncilController::class, 'startEvaluations'])->name('councils.start_evaluations');
        Route::delete('/councils/{council}/clear-evaluations', [AdviserCouncilController::class, 'clearEvaluations'])->name('councils.clear_evaluations');

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
        Route::resource('departments', AdminDepartmentController::class);

        // Evaluation Form management
        Route::get('/evaluation_forms', [AdminEvaluationFormController::class, 'index'])->name('evaluation_forms.index');
        Route::get('/evaluation_forms/edit', [AdminEvaluationFormController::class, 'edit'])->name('evaluation_forms.edit');
        Route::put('/evaluation_forms', [AdminEvaluationFormController::class, 'update'])->name('evaluation_forms.update');
        Route::get('/evaluation_forms/preview/{type?}', [AdminEvaluationFormController::class, 'preview'])->name('evaluation_forms.preview');

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


