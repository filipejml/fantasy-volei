<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VnlPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config()->set('services.api_sports_volleyball', [
            'base_url' => 'https://v1.volleyball.api-sports.io',
            'key' => 'test-key',
            'season' => 2026,
            'cache_seconds' => 900,
            'leagues' => [
                'masculino' => 100,
                'feminino' => 101,
            ],
        ]);
    }

    public function test_authenticated_user_can_see_vnl_scores_and_standings(): void
    {
        Http::fake([
            '*/games*' => Http::response([
                'errors' => [],
                'response' => [[
                    'id' => 1,
                    'date' => '2026-06-23T18:00:00+00:00',
                    'timestamp' => 1782237600,
                    'status' => ['long' => 'Finished'],
                    'teams' => [
                        'home' => ['name' => 'Brasil', 'logo' => null],
                        'away' => ['name' => 'Itália', 'logo' => null],
                    ],
                    'scores' => ['home' => 3, 'away' => 1],
                ]],
            ]),
            '*/standings*' => Http::response([
                'errors' => [],
                'response' => [[
                    'league' => ['id' => 100],
                    'standings' => [[[
                        'position' => 1,
                        'team' => ['name' => 'Brasil', 'logo' => null],
                        'games' => [
                            'played' => 4,
                            'win' => ['total' => 4],
                            'lose' => ['total' => 0],
                        ],
                        'sets' => ['for' => 12, 'against' => 2],
                        'points' => 12,
                    ]]],
                ]],
            ]),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('Brasil')
            ->assertSee('Itália')
            ->assertSee('3')
            ->assertSee('Classificação');

        Http::assertSentCount(2);
    }

    public function test_vnl_page_shows_configuration_message_without_api_key(): void
    {
        config()->set('services.api_sports_volleyball.key', null);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('vnl.index'))
            ->assertOk()
            ->assertSee('ainda não foi configurada');

        Http::assertNothingSent();
    }

    public function test_vnl_page_requires_authentication(): void
    {
        $this->get(route('vnl.index'))
            ->assertRedirect(route('login'));
    }
}
