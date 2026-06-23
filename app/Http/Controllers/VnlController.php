<?php

namespace App\Http\Controllers;

use App\Services\ApiSportsVolleyball;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class VnlController extends Controller
{
    public function index(Request $request, ApiSportsVolleyball $api): View
    {
        $genero = in_array($request->query('genero'), ['masculino', 'feminino'], true)
            ? $request->query('genero')
            : 'masculino';

        try {
            $dados = $api->vnl($genero);
            $erro = null;
        } catch (Throwable $exception) {
            report($exception);

            $dados = [
                'jogos' => [],
                'classificacao' => [],
                'temporada' => config('services.api_sports_volleyball.season'),
            ];
            $erro = $exception->getMessage();
        }

        return view('vnl.index', [
            'genero' => $genero,
            'jogos' => collect($dados['jogos'])
                ->sortBy(fn (array $jogo) => $jogo['timestamp'] ?? strtotime($jogo['date'] ?? 'now'))
                ->values(),
            'classificacao' => $this->standingsRows($dados['classificacao']),
            'temporada' => $dados['temporada'],
            'erro' => $erro,
        ]);
    }

    private function standingsRows(array $response): array
    {
        if ($response === []) {
            return [];
        }

        if (isset($response['position']) || isset($response['rank'])) {
            return [$response];
        }

        $rows = [];

        foreach ($response as $value) {
            if (is_array($value)) {
                $rows = [...$rows, ...$this->standingsRows($value)];
            }
        }

        return $rows;
    }
}
