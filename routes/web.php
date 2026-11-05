<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Przekierowanie domyślnego dashboardu Breeze na nasz panel wydatków
Route::get('/dashboard', function () {
    return redirect()->route('expenses.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Trasy wymagające zalogowania
Route::middleware('auth')->group(function () {
    // Profil użytkownika (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Konfiguracja i dołączanie do mieszkania
    Route::get('/flat/setup', [FlatController::class, 'setup'])->name('flats.setup');
    Route::post('/flat/create', [FlatController::class, 'create'])->name('flat.create');
    Route::post('/flat/join', [FlatController::class, 'join'])->name('flat.join');

    // Panel wydatków
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
});

require __DIR__.'/auth.php';
