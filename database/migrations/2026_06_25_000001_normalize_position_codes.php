<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapa = [
            'LEV' => ['sigla' => 'S', 'nome' => 'Levantador'],
            'PON' => ['sigla' => 'OH', 'nome' => 'Ponteiro'],
            'OPO' => ['sigla' => 'O', 'nome' => 'Oposto'],
            'CEN' => ['sigla' => 'MB', 'nome' => 'Central'],
            'LIB' => ['sigla' => 'L', 'nome' => 'Líbero'],
        ];

        DB::transaction(function () use ($mapa): void {
            foreach ($mapa as $antiga => $nova) {
                $origem = DB::table('posicaos')->where('sigla', $antiga)->first();

                if (! $origem) {
                    continue;
                }

                $destino = DB::table('posicaos')->where('sigla', $nova['sigla'])->first();

                if ($destino) {
                    DB::table('jogadors')
                        ->where('posicao_id', $origem->id)
                        ->update(['posicao_id' => $destino->id]);

                    DB::table('posicaos')->where('id', $origem->id)->delete();

                    continue;
                }

                DB::table('posicaos')
                    ->where('id', $origem->id)
                    ->update([
                        'sigla' => $nova['sigla'],
                        'nome' => $nova['nome'],
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        $mapa = [
            'S' => ['sigla' => 'LEV', 'nome' => 'Levantador'],
            'OH' => ['sigla' => 'PON', 'nome' => 'Ponteiro'],
            'O' => ['sigla' => 'OPO', 'nome' => 'Oposto'],
            'MB' => ['sigla' => 'CEN', 'nome' => 'Central'],
            'L' => ['sigla' => 'LIB', 'nome' => 'Líbero'],
        ];

        DB::transaction(function () use ($mapa): void {
            foreach ($mapa as $atual => $antiga) {
                DB::table('posicaos')
                    ->where('sigla', $atual)
                    ->update([
                        'sigla' => $antiga['sigla'],
                        'nome' => $antiga['nome'],
                        'updated_at' => now(),
                    ]);
            }
        });
    }
};
