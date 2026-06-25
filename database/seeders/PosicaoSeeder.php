<?php

namespace Database\Seeders;

use App\Models\Posicao;
use Illuminate\Database\Seeder;

class PosicaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posicoes = [
            ['nome' => 'Levantador', 'sigla' => 'S'],
            ['nome' => 'Oposto', 'sigla' => 'O'],
            ['nome' => 'Ponteiro', 'sigla' => 'OH'],
            ['nome' => 'Central', 'sigla' => 'MB'],
            ['nome' => 'Líbero', 'sigla' => 'L'],
        ];

        foreach ($posicoes as $posicao) {
            Posicao::updateOrCreate(['sigla' => $posicao['sigla']], $posicao);
        }
    }
}
