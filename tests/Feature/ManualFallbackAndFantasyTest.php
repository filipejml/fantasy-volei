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
        $fora = Selecao::create(['nome' => 'Itália', 'genero' => 'masculino', 'ativo' => true]);

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

    public function test_user_can_receive_and_save_a_team_suggestion(): void
    {
        $user = User::factory()->create();
        $selecao = Selecao::create(['nome' => 'Brasil', 'genero' => 'masculino', 'ativo' => true]);
        $posicao = Posicao::create(['nome' => 'Ponteiro', 'sigla' => 'PON']);

        for ($i = 1; $i <= 8; $i++) {
            Jogador::create([
                'selecao_id' => $selecao->id,
                'posicao_id' => $posicao->id,
                'nome' => "Jogador {$i}",
                'genero' => 'masculino',
                'valor_creditos' => 10,
                'media_pontos' => 20 - $i,
                'ativo' => true,
            ]);
        }

        $this->actingAs($user)->post(route('times.sugerir'), [
            'nome' => 'Seleção ideal',
            'genero' => 'masculino',
            'creditos_limite' => 50,
        ])->assertRedirect();

        $time = $user->times()->firstOrFail();
        $this->assertSame(5, $time->jogadores()->count());
        $this->assertSame('50.00', $time->creditos_usados);
    }
}
