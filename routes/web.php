<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// 1. Halaman Utama / Landing Page
Route::get('/', function () {
    return view('welcome');
});

// 2. Dashboard User (Setelah Login)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. Manajemen Profil User (Bawaan Laravel Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. FITUR CHAT REAL-TIME (Sudah Diperbaiki & Ditambahkan Sesuai Project 1)
Route::middleware('auth')->group(function () {
    // Menampilkan halaman utama aplikasi chat (Daftar Obrolan)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    
    // Fitur Papan Tulis: Mengambil semua daftar user lain untuk diajak chat baru
    Route::get('/chat/users', [ChatController::class, 'getUsers'])->name('chat.users');
    
    // Fitur Papan Tulis: Membuat room chat privat baru saat salah satu user diklik
    Route::post('/chat/create/{userId}', [ChatController::class, 'createConversation'])->name('chat.create');
    
    // Mengambil riwayat pesan lama dari room chat tertentu via AJAX GET
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    
    // Menyimpan pesan baru ke database & trigger broadcast via AJAX POST (Disesuaikan ke storeMessage)
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'storeMessage'])->name('chat.storeMessage');
});

// 5. Rute Autentikasi Login / Register (Bawaan Laravel Breeze)
require __DIR__.'/auth.php';