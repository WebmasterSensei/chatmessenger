<?php

use App\Http\Controllers\MessengerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login');
});



Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [MessengerController::class, 'index'])->name('dashboard');
    Route::post('/get-users', [MessengerController::class, 'getUsers'])->name('get.users');
    Route::get('/get-message', [MessengerController::class, 'getMessage'])->name('get.message');
    Route::post('/send-message', [MessengerController::class, 'sendMessage'])->name('send.message');
    Route::get('/search-users', [MessengerController::class, 'searchUser'])->name('search.users');
    Route::post('/upload-image', [MessengerController::class, 'uploadImage'])->name('upload.image');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
