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
            ['nome' => 'Levantador', 'sigla' => 'LEV'],
            ['nome' => 'Oposto', 'sigla' => 'OPO'],
            ['nome' => 'Ponteiro', 'sigla' => 'PON'],
            ['nome' => 'Central', 'sigla' => 'CEN'],
            ['nome' => 'Líbero', 'sigla' => 'LIB'],
        ];

        foreach ($posicoes as $posicao) {
            Posicao::updateOrCreate(['sigla' => $posicao['sigla']], $posicao);
        }
    }
}
