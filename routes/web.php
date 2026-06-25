<?php

use App\Http\Controllers\ClassificacaoController;
use App\Http\Controllers\JogadorController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\PosicaoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScrapingController;
use App\Http\Controllers\SelecaoController;
use App\Http\Controllers\TimeController;
use App\Http\Controllers\VnlController;
use App\Models\Partida;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    $inicioDoDia = now()->startOfDay();
    $fimDoDia = now()->endOfDay();

    $jogosHoje = Partida::with(['selecaoCasa', 'selecaoFora'])
        ->whereBetween('data_partida', [$inicioDoDia, $fimDoDia])
        ->orderBy('data_partida')
        ->get();

    return view('dashboard', [
        'jogosHoje' => $jogosHoje,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/vnl', [VnlController::class, 'index'])->name('vnl.index');
    Route::resource('meus-times', TimeController::class)
        ->parameters(['meus-times' => 'time'])
        ->names('times');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::patch('selecoes/{selecao}/status', [SelecaoController::class, 'status'])->name('selecoes.status');
        Route::resource('selecoes', SelecaoController::class)
            ->parameters(['selecoes' => 'selecao']);
        Route::resource('jogadores', JogadorController::class)
            ->parameters(['jogadores' => 'jogador']);
        Route::post('jogadores/atualizar-vw', [ScrapingController::class, 'atualizarJogadores'])->name('jogadores.atualizar-vw');
        Route::resource('posicoes', PosicaoController::class)
            ->parameters(['posicoes' => 'posicao']);
        Route::patch('partidas/{partida}/atualizar-placar', [PartidaController::class, 'atualizarPlacar'])->name('partidas.atualizar-placar');
        Route::resource('partidas', PartidaController::class)
            ->parameters(['partidas' => 'partida']);
        Route::resource('classificacoes', ClassificacaoController::class)
            ->except('show')
            ->parameters(['classificacoes' => 'classificacao']);
        Route::post('classificacoes/calcular', [ClassificacaoController::class, 'calcular'])->name('classificacoes.calcular');
        Route::get('scraping', [ScrapingController::class, 'index'])->name('scraping.index');
        Route::post('scraping/atualizar', [ScrapingController::class, 'atualizar'])->name('scraping.atualizar');
    });

require __DIR__.'/auth.php';
