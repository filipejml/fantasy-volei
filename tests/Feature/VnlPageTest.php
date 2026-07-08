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

    public function test_authenticated_user_can_see_persisted_scores(): void
    {
        $brasil = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        $italia = Selecao::create(['nome' => 'Italia', 'sigla' => 'ITA', 'genero' => 'masculino', 'ativo' => true]);

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

        $this->actingAs(User::factory()->create())
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('Brasil')
            ->assertSee('Italia')
            ->assertDontSee('Tabela importada ou mantida manualmente pelo administrador.');
    }

    public function test_vnl_classification_page_shows_standings(): void
    {
        $brasil = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);

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
            ->get(route('vnl.classificacao'))
            ->assertOk()
            ->assertSee('Tabela de classificação')
            ->assertSee('Brasil')
            ->assertSee('3:1');
    }

    public function test_vnl_page_works_without_scraped_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('Nenhuma partida cadastrada');
    }

    public function test_vnl_classification_page_works_without_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('vnl.classificacao'))
            ->assertOk()
            ->assertSee('Classificação ainda não cadastrada.');
    }

    public function test_vnl_page_shows_live_set_points(): void
    {
        $brasil = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        $italia = Selecao::create(['nome' => 'Italia', 'sigla' => 'ITA', 'genero' => 'masculino', 'ativo' => true]);

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

    public function test_vnl_page_groups_live_upcoming_and_finished_games(): void
    {
        $timeA = Selecao::create(['nome' => 'Ao Vivo A', 'sigla' => 'AVA', 'genero' => 'masculino', 'ativo' => true]);
        $timeB = Selecao::create(['nome' => 'Ao Vivo B', 'sigla' => 'AVB', 'genero' => 'masculino', 'ativo' => true]);
        $timeC = Selecao::create(['nome' => 'Proximo A', 'sigla' => 'PRA', 'genero' => 'masculino', 'ativo' => true]);
        $timeD = Selecao::create(['nome' => 'Proximo B', 'sigla' => 'PRB', 'genero' => 'masculino', 'ativo' => true]);
        $timeE = Selecao::create(['nome' => 'Finalizado A', 'sigla' => 'FIA', 'genero' => 'masculino', 'ativo' => true]);
        $timeF = Selecao::create(['nome' => 'Finalizado B', 'sigla' => 'FIB', 'genero' => 'masculino', 'ativo' => true]);

        Partida::create([
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $timeE->id,
            'selecao_fora_id' => $timeF->id,
            'data_partida' => '2026-06-23 10:00:00',
            'placar_casa' => 3,
            'placar_fora' => 0,
            'status' => 'encerrado',
            'origem' => 'manual',
        ]);

        Partida::create([
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $timeC->id,
            'selecao_fora_id' => $timeD->id,
            'data_partida' => '2026-06-24 18:00:00',
            'status' => 'agendado',
            'origem' => 'manual',
        ]);

        Partida::create([
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $timeA->id,
            'selecao_fora_id' => $timeB->id,
            'data_partida' => '2026-06-24 12:00:00',
            'placar_casa' => 1,
            'placar_fora' => 1,
            'status' => 'ao_vivo',
            'origem' => 'manual',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Ao vivo',
                'Ao Vivo A',
                'Próximos jogos',
                'Proximo A',
                'Finalizados',
                'Finalizado A',
            ]);
    }

    public function test_vnl_pages_require_authentication(): void
    {
        $this->get(route('vnl.index'))->assertRedirect(route('login'));
        $this->get(route('vnl.classificacao'))->assertRedirect(route('login'));
    }
}
