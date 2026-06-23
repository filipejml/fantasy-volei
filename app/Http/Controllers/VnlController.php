<?php

namespace App\Http\Controllers;

use App\Models\Classificacao;
use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VnlController extends Controller
{
    public function index(Request $request): View
    {
        $genero = in_array($request->query('genero'), ['masculino', 'feminino'], true)
            ? $request->query('genero')
            : 'masculino';
        $temporada = (int) $request->query('temporada', config('services.volleyball_world.season'));

        return view('vnl.index', [
            'genero' => $genero,
            'jogos' => Partida::with(['selecaoCasa', 'selecaoFora'])
                ->where('genero', $genero)
                ->where('temporada', $temporada)
                ->orderBy('data_partida')
                ->get(),
            'classificacao' => Classificacao::with('selecao')
                ->where('genero', $genero)
                ->where('temporada', $temporada)
                ->orderBy('posicao')
                ->get(),
            'temporada' => $temporada,
        ]);
    }
}
