<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ApiSportsVolleyball
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function vnl(string $genero): array
    {
        $config = config('services.api_sports_volleyball');
        $leagueId = $config['leagues'][$genero] ?? null;
        $season = (int) $config['season'];

        if (blank($config['key']) || blank($leagueId)) {
            throw new RuntimeException('A integração com a API-SPORTS ainda não foi configurada.');
        }

        $cacheKey = "api-sports-volleyball:vnl:{$genero}:{$leagueId}:{$season}";

        return Cache::remember($cacheKey, $config['cache_seconds'], function () use ($config, $leagueId, $season) {
            return [
                'jogos' => $this->request('games', [
                    'league' => $leagueId,
                    'season' => $season,
                ]),
                'classificacao' => $this->request('standings', [
                    'league' => $leagueId,
                    'season' => $season,
                ]),
                'temporada' => $season,
            ];
        });
    }

    private function request(string $endpoint, array $query): array
    {
        $response = $this->http
            ->baseUrl(config('services.api_sports_volleyball.base_url'))
            ->withHeaders([
                'x-apisports-key' => config('services.api_sports_volleyball.key'),
            ])
            ->acceptJson()
            ->timeout(15)
            ->retry(2, 300)
            ->get($endpoint, $query)
            ->throw()
            ->json();

        $errors = $response['errors'] ?? [];

        if (! empty($errors)) {
            $message = is_array($errors)
                ? implode(' ', array_map(
                    fn ($key, $value) => is_string($key)
                        ? "{$key}: ".(is_scalar($value) ? $value : json_encode($value))
                        : (is_scalar($value) ? (string) $value : json_encode($value)),
                    array_keys($errors),
                    $errors
                ))
                : (string) $errors;

            throw new RuntimeException($message ?: 'A API-SPORTS retornou um erro.');
        }

        return $response['response'] ?? [];
    }
}
