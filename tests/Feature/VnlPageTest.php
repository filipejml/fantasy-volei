<?php

namespace Tests\Feature;

use App\Models\Classificacao;
use App\Models\Partida;
use App\Models\Selecao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VnlPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_persisted_scores_and_standings(): void
    {
        $brasil = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        $italia = Selecao::create(['nome' => 'Itália', 'sigla' => 'ITA', 'genero' => 'masculino', 'ativo' => true]);

        Partida::create([
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $brasil->id,
            'selecao_fora_id' => $italia->id,
            'data_partida' => '2026-06-23 18:00:00',
            'placar_casa' => 3,
            'placar_fora' => 1,
            'status' => 'encerrado',
            'origem' => 'manual',
        ]);

        Classificacao::create([
            'selecao_id' => $brasil->id,
            'genero' => 'masculino',
            'temporada' => 2026,
            'posicao' => 1,
            'jogos' => 1,
            'vitorias' => 1,
            'derrotas' => 0,
            'pontos' => 3,
            'sets_pro' => 3,
            'sets_contra' => 1,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('Brasil')
            ->assertSee('Itália')
            ->assertSee('Classificação');
    }

    public function test_vnl_page_works_without_scraped_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('Nenhuma partida cadastrada')
            ->assertSee('Classificação ainda não cadastrada');
    }

    public function test_vnl_page_shows_live_set_points(): void
    {
        $brasil = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        $italia = Selecao::create(['nome' => 'Itália', 'sigla' => 'ITA', 'genero' => 'masculino', 'ativo' => true]);

        Partida::create([
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $brasil->id,
            'selecao_fora_id' => $italia->id,
            'data_partida' => '2026-06-24 10:00:00',
            'placar_casa' => 1,
            'placar_fora' => 0,
            'sets' => [
                ['pointsTeamA' => 25, 'pointsTeamB' => 20],
                ['pointsTeamA' => 12, 'pointsTeamB' => 9],
            ],
            'status' => 'ao_vivo',
            'origem' => 'manual',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('Ao Vivo')
            ->assertSee('Set atual: 12 x 9')
            ->assertSee('window.location.reload');
    }

    public function test_vnl_page_requires_authentication(): void
    {
        $this->get(route('vnl.index'))->assertRedirect(route('login'));
    }
}
