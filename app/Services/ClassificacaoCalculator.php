<?php

namespace App\Services;

use App\Models\Classificacao;
use App\Models\Partida;
use Illuminate\Support\Facades\DB;

class ClassificacaoCalculator
{
    public function calcular(string $genero, int $temporada): int
    {
        $tabela = [];
        $partidas = Partida::where('genero', $genero)
            ->where('temporada', $temporada)
            ->where('status', 'encerrado')
            ->whereNotNull('placar_casa')
            ->whereNotNull('placar_fora')
            ->get();

        foreach ($partidas as $partida) {
            foreach ([$partida->selecao_casa_id, $partida->selecao_fora_id] as $id) {
                $tabela[$id] ??= [
                    'jogos' => 0, 'vitorias' => 0, 'derrotas' => 0, 'pontos' => 0,
                    'sets_pro' => 0, 'sets_contra' => 0, 'pontos_pro' => 0, 'pontos_contra' => 0,
                ];
            }

            $casa = &$tabela[$partida->selecao_casa_id];
            $fora = &$tabela[$partida->selecao_fora_id];
            $casa['jogos']++;
            $fora['jogos']++;
            $casa['sets_pro'] += $partida->placar_casa;
            $casa['sets_contra'] += $partida->placar_fora;
            $fora['sets_pro'] += $partida->placar_fora;
            $fora['sets_contra'] += $partida->placar_casa;

            foreach ($partida->sets ?? [] as $set) {
                $casa['pontos_pro'] += $set['pointsTeamA'] ?? 0;
                $casa['pontos_contra'] += $set['pointsTeamB'] ?? 0;
                $fora['pontos_pro'] += $set['pointsTeamB'] ?? 0;
                $fora['pontos_contra'] += $set['pointsTeamA'] ?? 0;
            }

            $casaVenceu = $partida->placar_casa > $partida->placar_fora;
            $vencedor = &$tabela[$casaVenceu ? $partida->selecao_casa_id : $partida->selecao_fora_id];
            $perdedor = &$tabela[$casaVenceu ? $partida->selecao_fora_id : $partida->selecao_casa_id];
            $vencedor['vitorias']++;
            $perdedor['derrotas']++;
            $jogoLongo = min($partida->placar_casa, $partida->placar_fora) === 2;
            $vencedor['pontos'] += $jogoLongo ? 2 : 3;
            $perdedor['pontos'] += $jogoLongo ? 1 : 0;
        }

        uasort($tabela, fn ($a, $b) => [$b['pontos'], $b['vitorias'], $b['sets_pro'] - $b['sets_contra']] <=> [$a['pontos'], $a['vitorias'], $a['sets_pro'] - $a['sets_contra']]);

        DB::transaction(function () use ($tabela, $genero, $temporada) {
            $posicao = 1;
            foreach ($tabela as $selecaoId => $dados) {
                Classificacao::updateOrCreate(
                    compact('genero', 'temporada') + ['selecao_id' => $selecaoId],
                    [
                        ...$dados,
                        'posicao' => $posicao++,
                        'set_ratio' => $dados['sets_contra'] > 0 ? $dados['sets_pro'] / $dados['sets_contra'] : null,
                        'ponto_ratio' => $dados['pontos_contra'] > 0 ? $dados['pontos_pro'] / $dados['pontos_contra'] : null,
                        'origem' => 'calculada',
                        'importado_em' => now(),
                    ]
                );
            }
        });

        return count($tabela);
    }
}
