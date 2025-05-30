<?php

use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});



Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [MessengerController::class, 'index'])->name('dashboard');
    Route::post('/get-users', [MessengerController::class, 'getUsers'])->name('get.users');
    Route::get('/get-message', [MessengerController::class, 'getMessage'])->name('get.message');
    Route::post('/send-message', [MessengerController::class, 'sendMessage'])->name('send.message');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
