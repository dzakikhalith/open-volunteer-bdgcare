<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventQuestionController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Public\EventPublicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\RegistrationTrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public events
Route::get('/events', [EventPublicController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventPublicController::class, 'show'])->name('events.show');

// Volunteer registration
Route::get('/events/{event:slug}/daftar', [RegistrationController::class, 'create'])->name('events.register');
Route::post('/events/{event:slug}/daftar', [RegistrationController::class, 'store'])->name('events.register.store')->middleware('throttle:10,1');

Route::get('/pendaftaran', [RegistrationTrackingController::class, 'track'])->name('registrations.track');
Route::post('/pendaftaran', [RegistrationTrackingController::class, 'lookup'])->name('registrations.lookup')->middleware('throttle:20,1');
Route::get('/pendaftaran/{registration}', [RegistrationTrackingController::class, 'show'])
    ->middleware('signed')
    ->name('registrations.show');

// AUTH ADMIN
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout')->middleware('auth');

// ADMIN
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('events', EventController::class)->except(['show']);
    Route::get('/events/{event}/questions/preview', [EventQuestionController::class, 'preview'])
        ->name('events.questions.preview');
    Route::post('/events/{event}/questions/{question}/move', [EventQuestionController::class, 'move'])
        ->name('events.questions.move');
    Route::resource('events.questions', EventQuestionController::class)->except(['show'])->scoped();
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('admins', AdminUserController::class)->except(['show']);

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/registrations', [AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/export', [AdminRegistrationController::class, 'export'])->name('registrations.export');
    Route::get('/registrations/{registration}/answers/{answer}/download', [AdminRegistrationController::class, 'downloadAnswer'])
        ->name('registrations.answers.download');
    Route::get('/registrations/{registration}', [AdminRegistrationController::class, 'show'])->name('registrations.show');
    Route::patch('/registrations/{registration}/status', [AdminRegistrationController::class, 'updateStatus'])->name('registrations.update-status');
});
