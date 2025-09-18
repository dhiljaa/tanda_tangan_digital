<?php
// routes/web.php - Manual Authentication

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes (manual)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Handle GET requests to logout - redirect to dashboard with message
Route::get('/logout', function () {
    return redirect()->route('dashboard')->with('error', 'Please use the logout button to sign out securely.');
})->middleware('auth');

// Public verification routes - HASH ONLY
Route::get('/verify/{hash}', [VerificationController::class, 'show'])
    ->name('verify.show');

Route::get('/verify', [VerificationController::class, 'index'])
    ->name('verify.index');

Route::post('/verify', [VerificationController::class, 'verify'])
    ->name('verify.post');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('documents', DocumentController::class);
    
    // Additional document routes
    Route::get('/documents/search', [DocumentController::class, 'search'])
        ->name('documents.search');
    Route::get('/documents/{document}/duplicate', [DocumentController::class, 'duplicate'])
        ->name('documents.duplicate');
    Route::get('/documents/number/{documentNumber}', [DocumentController::class, 'getByNumber'])
        ->name('documents.by-number');
    Route::get('/documents/report/generate', [DocumentController::class, 'generateReport'])
        ->name('documents.report');

    // Profile routes
   
    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});