<?php

use App\Http\Controllers\JogadorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelecaoController;
use App\Http\Controllers\VnlController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/vnl', [VnlController::class, 'index'])->name('vnl.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('selecoes', SelecaoController::class)
            ->parameters(['selecoes' => 'selecao']);
        Route::resource('jogadores', JogadorController::class)
            ->parameters(['jogadores' => 'jogador']);
    });

require __DIR__.'/auth.php';
