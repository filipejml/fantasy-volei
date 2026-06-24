<?php

namespace Tests\Feature;

use App\Models\Classificacao;
use App\Models\Partida;
use App\Models\ScrapingLog;
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
