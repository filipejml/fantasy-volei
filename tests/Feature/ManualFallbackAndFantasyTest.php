<?php

namespace Tests\Feature;

use App\Models\Jogador;
use App\Models\Posicao;
use App\Models\Selecao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualFallbackAndFantasyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_match_manually(): void
    {
        $admin = User::factory()->create(['role' => 0]);
        $casa = Selecao::create(['nome' => 'Brasil', 'genero' => 'masculino', 'ativo' => true]);
        $fora = Selecao::create(['nome' => 'Italia', 'genero' => 'masculino', 'ativo' => true]);

        $this->actingAs($admin)->post(route('admin.partidas.store'), [
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $casa->id,
            'selecao_fora_id' => $fora->id,
            'data_partida' => '2026-06-23 18:00:00',
            'placar_casa' => 3,
            'placar_fora' => 1,
            'status' => 'encerrado',
        ])->assertRedirect();

        $this->assertDatabaseHas('partidas', [
            'selecao_casa_id' => $casa->id,
            'origem' => 'manual',
        ]);
    }

    public function test_user_can_render_and_save_my_team_page(): void
    {
        $user = User::factory()->create();
        $jogadores = $this->criarJogadoresParaEscalacao();

        $this->actingAs($user)
            ->get(route('times.create', ['genero' => 'masculino']))
            ->assertOk()
            ->assertSee('Montar time fantasy')
            ->assertSee('Atleta OH 1')
            ->assertSee('Escalacao aleatoria')
            ->assertSee('Titulares')
            ->assertSee('Reservas');

        $this->actingAs($user)
            ->post(route('times.store'), [
                'nome' => 'Meu Brasil',
                'genero' => 'masculino',
                'creditos_limite' => 120,
                'titulares' => [
                    'OH' => $jogadores['titulares']['OH']->pluck('id')->all(),
                    'MB' => $jogadores['titulares']['MB']->pluck('id')->all(),
                    'O' => $jogadores['titulares']['O']->pluck('id')->all(),
                    'S' => $jogadores['titulares']['S']->pluck('id')->all(),
                ],
                'reservas' => [
                    'L' => $jogadores['reservas']['L']->pluck('id')->all(),
                    'S' => $jogadores['reservas']['S']->pluck('id')->all(),
                    'O' => $jogadores['reservas']['O']->pluck('id')->all(),
                    'MB' => $jogadores['reservas']['MB']->pluck('id')->all(),
                ],
            ])
            ->assertRedirect();

        $time = $user->times()->with('jogadores')->firstOrFail();

        $this->assertSame('Meu Brasil', $time->nome);
        $this->assertSame(12, $time->jogadores->count());
        $this->assertSame('120.00', $time->creditos_usados);
        $this->assertDatabaseHas('time_jogadors', [
            'time_id' => $time->id,
            'jogador_id' => $jogadores['reservas']['L']->first()->id,
            'tipo' => 'reserva',
            'slot' => 'L_1',
        ]);

        $this->actingAs($user)
            ->get(route('times.index'))
            ->assertOk()
            ->assertSee('Meu Brasil')
            ->assertSee('Atleta OH 1')
            ->assertSee('12/12')
            ->assertSee('C$ 120');
    }

    private function criarJogadoresParaEscalacao(): array
    {
        $selecao = Selecao::create(['nome' => 'Brasil', 'genero' => 'masculino', 'ativo' => true]);
        $posicoes = collect([
            'OH' => 'Ponteiro',
            'MB' => 'Central',
            'O' => 'Oposto',
            'S' => 'Levantador',
            'L' => 'Libero',
        ])->map(fn (string $nome, string $sigla) => Posicao::create(['nome' => $nome, 'sigla' => $sigla]));

        $criar = function (string $sigla, int $quantidade, string $prefixo) use ($selecao, $posicoes) {
            return collect(range(1, $quantidade))->map(fn (int $i) => Jogador::create([
                'selecao_id' => $selecao->id,
                'posicao_id' => $posicoes[$sigla]->id,
                'nome' => "Atleta {$prefixo} {$i}",
                'genero' => 'masculino',
                'valor_creditos' => 10,
                'media_pontos' => 20 - $i,
                'ativo' => true,
            ]));
        };

        return [
            'titulares' => [
                'OH' => $criar('OH', 2, 'OH'),
                'MB' => $criar('MB', 2, 'MB'),
                'O' => $criar('O', 1, 'O'),
                'S' => $criar('S', 1, 'S'),
            ],
            'reservas' => [
                'L' => $criar('L', 1, 'L'),
                'S' => $criar('S', 1, 'S Reserva'),
                'O' => $criar('O', 2, 'O Reserva'),
                'MB' => $criar('MB', 2, 'MB Reserva'),
            ],
        ];
    }
}
