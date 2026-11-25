<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FlatBillController;
use App\Http\Controllers\FlatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlatMessageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('expenses.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/flat/setup', [FlatController::class, 'setup'])->name('flats.setup');
    Route::post('/flat/create', [FlatController::class, 'create'])->name('flat.create');
    Route::post('/flat/join', [FlatController::class, 'join'])->name('flat.join');

    Route::post('/flat/messages', [FlatMessageController::class, 'store'])->name('flat.messages.store');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::post('/flat-bills', [FlatBillController::class, 'store'])->name('flat-bills.store');
    Route::patch('/flat-bills/{bill}', [FlatBillController::class, 'update'])->name('flat-bills.update');
    Route::delete('/flat-bills/{bill}', [FlatBillController::class, 'destroy'])->name('flat-bills.destroy');

    Route::patch('/flat/name', [FlatController::class, 'updateName'])->name('flat.update-name');
    Route::post('/flat/leave', [FlatController::class, 'leave'])->name('flat.leave');

    // Zgłoszenie spłaty udziału w wydatku oraz akceptacja / cofnięcie
        Route::post('/expenses/{expense}/submit-payment', [ExpenseController::class, 'submitPayment'])->name('expenses.submit-payment');
        Route::patch('/expense-payments/{payment}/approve', [ExpenseController::class, 'approvePayment'])->name('expense-payments.approve');
        Route::patch('/expense-payments/{payment}/reset', [ExpenseController::class, 'resetPayment'])->name('expense-payments.reset');

    // Zgłoszenie wpłaty przez mieszkańca oraz akceptacja / cofnięcie przez administratora
    Route::post('/flat-bills/{bill}/submit-payment', [FlatBillController::class, 'submitPayment'])->name('flat-bills.submit-payment');
    Route::patch('/flat-bill-payments/{payment}/approve', [FlatBillController::class, 'approvePayment'])->name('flat-bill-payments.approve');
    Route::patch('/flat-bill-payments/{payment}/reset', [FlatBillController::class, 'resetPayment'])->name('flat-bill-payments.reset');
});

require __DIR__.'/auth.php';
