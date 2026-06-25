<?php

namespace Tests\Feature;

use App\Models\Classificacao;
use App\Models\Jogador;
use App\Models\Partida;
use App\Models\ScrapingLog;
use App\Models\Selecao;
use App\Services\VolleyballWorldScraper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VolleyballWorldScraperTest extends TestCase
{
    use RefreshDatabase;

    public function test_scraper_imports_games_and_standings_without_duplicates(): void
    {
        config()->set('services.volleyball_world', [
            'base_url' => 'https://en.volleyballworld.com',
            'season' => 2026,
            'tournaments' => '1661;1662',
            'schedule_from' => '2026-06-01',
            'schedule_to' => '2026-08-15',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/api/v1/volley-tournament/')) {
                return Http::response([
                    'allTeams' => [
                        ['no' => 1, 'code' => 'BRA', 'name' => 'Brazil', 'translatedName' => 'Brazil', 'img' => 'https://img/bra'],
                        ['no' => 2, 'code' => 'ITA', 'name' => 'Italy', 'translatedName' => 'Italy', 'img' => 'https://img/ita'],
                        ['no' => 3, 'code' => 'POL', 'name' => 'Poland', 'translatedName' => 'Poland', 'img' => 'https://img/pol', 'gender' => 'Men'],
                    ],
                    'matches' => [[
                        'matchNo' => 10,
                        'matchDateUtc' => '2026-06-03T10:00:00Z',
                        'gender' => 'Men',
                        'teamANo' => 1,
                        'teamBNo' => 2,
                        'teamAScore' => 3,
                        'teamBScore' => 1,
                        'matchStatus' => 2,
                        'sets' => [['pointsTeamA' => 25, 'pointsTeamB' => 20]],
                        'roundName' => 'Week 1',
                        'city' => 'Rio',
                        'country' => 'Brazil',
                        'matchCenterUrl' => '/match/10',
                    ]],
                ]);
            }

            return Http::response($this->standingsHtml());
        });

        $scraper = app(VolleyballWorldScraper::class);
        $scraper->atualizarTudo();
        $scraper->atualizarTudo();

        $this->assertDatabaseCount('partidas', 1);
        $this->assertDatabaseCount('selecoes', 4);
        $this->assertDatabaseHas('selecoes', [
            'nome' => 'Polônia',
            'sigla' => 'POL',
            'genero' => 'masculino',
        ]);
        $this->assertDatabaseHas('selecoes', [
            'nome' => 'Itália',
            'sigla' => 'ITA',
            'genero' => 'masculino',
        ]);
        $this->assertDatabaseHas('selecoes', [
            'nome' => 'Brasil',
            'sigla' => 'BRA',
            'genero' => 'masculino',
        ]);
        $this->assertSame(1, Partida::first()->placar_fora);
        $this->assertGreaterThanOrEqual(1, Classificacao::count());
        $this->assertDatabaseHas('scraping_logs', ['status' => 'sucesso']);
        $this->assertSame(2, ScrapingLog::count());
    }

    public function test_scraper_ignores_negative_placeholder_scores(): void
    {
        config()->set('services.volleyball_world', [
            'base_url' => 'https://en.volleyballworld.com',
            'season' => 2026,
            'tournaments' => '1661;1662',
            'schedule_from' => '2026-06-01',
            'schedule_to' => '2026-08-15',
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/api/v1/volley-tournament/')) {
                return Http::response([
                    'allTeams' => [
                        ['no' => 1, 'code' => 'CHN', 'name' => 'China', 'translatedName' => 'China', 'img' => 'https://img/chn'],
                        ['no' => 2, 'code' => 'TUR', 'name' => 'Turkey', 'translatedName' => 'Turkey', 'img' => 'https://img/tur'],
                    ],
                    'matches' => [[
                        'matchNo' => 11,
                        'matchDateUtc' => '2026-06-24T11:00:00Z',
                        'gender' => 'Men',
                        'teamANo' => 1,
                        'teamBNo' => 2,
                        'teamAScore' => -2147483648,
                        'teamBScore' => -2147483648,
                        'matchStatus' => 0,
                        'sets' => [],
                        'roundName' => 'Week 2',
                        'city' => 'Glywice',
                        'country' => 'Poland',
                    ]],
                ]);
            }

            return Http::response($this->standingsHtml());
        });

        app(VolleyballWorldScraper::class)->atualizarTudo();

        $partida = Partida::first();

        $this->assertNull($partida->placar_casa);
        $this->assertNull($partida->placar_fora);
    }

    public function test_scraper_imports_players_from_team_rosters(): void
    {
        config()->set('services.volleyball_world.base_url', 'https://en.volleyballworld.com');

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/teams/men/') && ! str_contains($request->url(), '/players/')) {
                return Http::response('
                    <a href="/volleyball/competitions/volleyball-nations-league/teams/men/8601/schedule/">Brazil BRA</a>
                ');
            }

            if (str_contains($request->url(), '/teams/women/') && ! str_contains($request->url(), '/players/')) {
                return Http::response('');
            }

            if (str_contains($request->url(), '/teams/men/8601/players/')) {
                return Http::response('
                    <a href="/volleyball/players/100">1</a><a href="/volleyball/players/100">Bruno</a><a href="/volleyball/players/100">Rezende</a><a href="/volleyball/players/100">S</a>
                    <a href="/volleyball/players/101">12</a><a href="/volleyball/players/101">Lucarelli</a><a href="/volleyball/players/101">OH</a>
                    <a href="/volleyball/players/102">23</a><a href="/volleyball/players/102">Maique</a><a href="/volleyball/players/102">L</a>
                ');
            }

            return Http::response('');
        });

        $log = app(VolleyballWorldScraper::class)->atualizarJogadores();

        $this->assertSame('sucesso', $log->status, $log->mensagem);
        $this->assertDatabaseHas('selecoes', ['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino']);
        $this->assertDatabaseHas('posicaos', ['sigla' => 'S']);
        $this->assertDatabaseHas('posicaos', ['sigla' => 'OH']);
        $this->assertDatabaseHas('posicaos', ['sigla' => 'L']);
        $this->assertDatabaseHas('jogadors', ['nome' => 'Bruno Rezende', 'genero' => 'masculino']);
        $this->assertDatabaseHas('jogadors', ['nome' => 'Lucarelli', 'genero' => 'masculino']);
        $this->assertDatabaseHas('jogadors', ['nome' => 'Maique', 'genero' => 'masculino']);
        $this->assertSame(3, Jogador::count());
    }

    public function test_scraper_imports_players_using_existing_volleyball_world_team_ids(): void
    {
        config()->set('services.volleyball_world.base_url', 'https://en.volleyballworld.com');

        Selecao::create([
            'nome' => 'Brasil',
            'sigla' => 'BRA',
            'genero' => 'masculino',
            'external_ref' => '8601',
            'ativo' => true,
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/teams/men/8601/players/')) {
                return Http::response('
                    <a href="/volleyball/players/200">1</a><a href="/volleyball/players/200">Darlan</a><a href="/volleyball/players/200">O</a>
                ');
            }

            return Http::response('', 404);
        });

        $log = app(VolleyballWorldScraper::class)->atualizarJogadores();

        $this->assertSame('sucesso', $log->status, $log->mensagem);
        $this->assertDatabaseHas('posicaos', ['sigla' => 'O']);
        $this->assertDatabaseHas('jogadors', ['nome' => 'Darlan', 'genero' => 'masculino']);
    }

    public function test_scraper_does_not_reactivate_disabled_selection_or_players(): void
    {
        config()->set('services.volleyball_world.base_url', 'https://en.volleyballworld.com');

        $selecao = Selecao::create([
            'nome' => 'Brasil',
            'sigla' => 'BRA',
            'genero' => 'masculino',
            'external_ref' => '8601',
            'ativo' => false,
        ]);
        $posicao = \App\Models\Posicao::create(['nome' => 'Oposto', 'sigla' => 'O']);

        Jogador::create([
            'selecao_id' => $selecao->id,
            'posicao_id' => $posicao->id,
            'nome' => 'Darlan',
            'genero' => 'masculino',
            'valor_creditos' => 10,
            'media_pontos' => 0,
            'ativo' => false,
        ]);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/teams/men/8601/players/')) {
                return Http::response('
                    <a href="/volleyball/players/200">1</a><a href="/volleyball/players/200">Darlan</a><a href="/volleyball/players/200">O</a>
                ');
            }

            return Http::response('', 404);
        });

        app(VolleyballWorldScraper::class)->atualizarJogadores();

        $this->assertDatabaseHas('selecoes', ['id' => $selecao->id, 'ativo' => false]);
        $this->assertDatabaseHas('jogadors', ['nome' => 'Darlan', 'ativo' => false]);
    }

    private function standingsHtml(): string
    {
        return <<<'HTML'
        <table><tr class="vbw-o-table__row">
          <td class="position">1</td>
          <td class="team">
            <div class="vbw-mu__team__logo"><img src="https://img/bra"></div>
            <div class="vbw-mu__team__name"><a>Brazil</a></div>
            <div class="vbw-mu__team__name vbw-mu__team__name--abbr"><a>BRA</a></div>
          </td>
          <td class="matchestotal">4</td><td class="matcheswon">4</td><td class="matcheslost">0</td>
          <td class="matchpoints">12</td><td class="setswon">12</td><td class="setslost">2</td>
          <td class="setsratio">6.000</td><td class="pointswon">350</td><td class="pointslost">300</td>
          <td class="pointsratio">1.166</td>
        </tr></table>
        HTML;
    }
}
