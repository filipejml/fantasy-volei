<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Selecao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_todays_games(): void
    {
        $this->travelTo('2026-06-24 10:00:00');

        $brasil = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        $italia = Selecao::create(['nome' => 'Italia', 'sigla' => 'ITA', 'genero' => 'masculino', 'ativo' => true]);
        $japao = Selecao::create(['nome' => 'Japao', 'sigla' => 'JPN', 'genero' => 'feminino', 'ativo' => true]);

        Partida::create([
            'genero' => 'masculino',
            'temporada' => 2026,
            'selecao_casa_id' => $brasil->id,
            'selecao_fora_id' => $italia->id,
            'data_partida' => '2026-06-24 18:00:00',
            'placar_casa' => -2147483648,
            'placar_fora' => -2147483648,
            'status' => 'agendado',
            'origem' => 'manual',
        ]);

        Partida::create([
            'genero' => 'feminino',
            'temporada' => 2026,
            'selecao_casa_id' => $japao->id,
            'selecao_fora_id' => $brasil->id,
            'data_partida' => '2026-06-25 18:00:00',
            'status' => 'agendado',
            'origem' => 'manual',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Jogos de hoje')
            ->assertSee('Brasil')
            ->assertSee('Italia')
            ->assertSeeTextInOrder(['Brasil', '0', 'Italia', '0'])
            ->assertDontSee('-2147483648')
            ->assertDontSee('Japao');
    }

    public function test_dashboard_shows_live_set_points(): void
    {
        $this->travelTo('2026-06-24 10:00:00');

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
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ao vivo')
            ->assertSee('Set atual: 12 x 9')
            ->assertSee('window.location.reload');
    }

    public function test_admin_links_are_in_navigation_not_dashboard_panel(): void
    {
        $this->actingAs(User::factory()->create(['role' => 0]))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Admin')
            ->assertSee('Seleções')
            ->assertSee('Classificação')
            ->assertSee('Posições')
            ->assertSee('Atualizar VNL')
            ->assertDontSee('Painel administrativo')
            ->assertDontSee('Gerenciar seleções')
            ->assertDontSee('Gerenciar jogadores');
    }

    public function test_regular_user_does_not_see_admin_navigation(): void
    {
        $this->actingAs(User::factory()->create(['role' => 1]))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Admin')
            ->assertDontSee('Seleções')
            ->assertDontSee('Atualizar VNL');
    }
}
