<?php

namespace App\Services;

use App\Models\Classificacao;
use App\Models\Jogador;
use App\Models\Partida;
use App\Models\Posicao;
use App\Models\ScrapingLog;
use App\Models\Selecao;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class VolleyballWorldScraper
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function atualizarTudo(): ScrapingLog
    {
        $log = ScrapingLog::create([
            'tipo' => 'vnl_completo',
            'status' => 'executando',
            'source_url' => $this->baseUrl(),
            'iniciado_em' => now(),
        ]);

        $detalhes = [];
        $processados = 0;

        try {
            $detalhes['partidas'] = $this->importarPartidas();
            $processados += $detalhes['partidas']['partidas'];

            foreach (['masculino', 'feminino'] as $genero) {
                try {
                    $resultado = $this->importarClassificacao($genero);
                    $detalhes["classificacao_{$genero}"] = $resultado;
                    $processados += $resultado['linhas'];
                } catch (Throwable $exception) {
                    $detalhes["classificacao_{$genero}"] = ['erro' => $exception->getMessage()];
                }
            }

            $temErro = collect($detalhes)->contains(fn ($item) => isset($item['erro']));

            $log->update([
                'status' => $temErro ? 'parcial' : 'sucesso',
                'mensagem' => $temErro
                    ? 'Importação concluída com falhas parciais. Os dados manuais foram preservados.'
                    : 'Dados da VNL atualizados com sucesso.',
                'detalhes' => $detalhes,
                'registros_processados' => $processados,
                'finalizado_em' => now(),
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'erro',
                'mensagem' => $exception->getMessage(),
                'detalhes' => ['exception' => $exception::class],
                'registros_processados' => $processados,
                'finalizado_em' => now(),
            ]);
        }

        return $log->fresh();
    }

    public function importarPartidas(): array
    {
        $url = sprintf(
            '%s/api/v1/volley-tournament/%s/%s/%s',
            $this->baseUrl(),
            config('services.volleyball_world.schedule_from'),
            config('services.volleyball_world.schedule_to'),
            config('services.volleyball_world.tournaments')
        );

        $payload = $this->get($url)->json();

        if (! is_array($payload) || ! isset($payload['matches'], $payload['allTeams'])) {
            throw new RuntimeException('A estrutura de jogos da Volleyball World mudou.');
        }

        $times = collect($payload['allTeams'])->keyBy('no');
        $generosPorTime = $this->generosPorTime($payload);
        $selecoes = [];

        DB::transaction(function () use ($payload, $times, $generosPorTime, &$selecoes, $url) {
            foreach ($times as $time) {
                if ($this->timeIndefinido($time)) {
                    continue;
                }

                foreach (($generosPorTime[(string) ($time['no'] ?? '')] ?? []) as $genero) {
                    $selecao = $this->salvarSelecao($time, $genero, $url);
                    $selecoes[$selecao->id] = true;
                }
            }

            foreach ($payload['matches'] as $jogo) {
                $genero = $this->normalizarGenero($jogo['gender'] ?? null) ?? 'masculino';

                $casa = $times->get($jogo['teamANo'] ?? null);
                $fora = $times->get($jogo['teamBNo'] ?? null);

                if (! $casa || ! $fora || $this->timeIndefinido($casa) || $this->timeIndefinido($fora)) {
                    continue;
                }

                $selecaoCasa = $this->salvarSelecao($casa, $genero, $url);
                $selecaoFora = $this->salvarSelecao($fora, $genero, $url);
                $selecoes[$selecaoCasa->id] = true;
                $selecoes[$selecaoFora->id] = true;

                $matchNo = (string) ($jogo['matchNo'] ?? '');
                $hash = $matchNo !== ''
                    ? "vw-vnl-".config('services.volleyball_world.season')."-{$matchNo}"
                    : hash('sha256', implode('|', [
                        $genero,
                        $jogo['matchDateUtc'] ?? '',
                        $selecaoCasa->id,
                        $selecaoFora->id,
                    ]));

                Partida::updateOrCreate(
                    ['external_hash' => $hash],
                    [
                        'genero' => $genero,
                        'temporada' => config('services.volleyball_world.season'),
                        'fase' => $jogo['phase'] ?? null,
                        'rodada' => $jogo['roundName'] ?? data_get($jogo, 'pool.name'),
                        'local' => collect([$jogo['city'] ?? null, $jogo['country'] ?? null])->filter()->join(', '),
                        'selecao_casa_id' => $selecaoCasa->id,
                        'selecao_fora_id' => $selecaoFora->id,
                        'data_partida' => Carbon::parse($jogo['matchDateUtc']),
                        'placar_casa' => $this->normalizarPlacar($jogo['teamAScore'] ?? null),
                        'placar_fora' => $this->normalizarPlacar($jogo['teamBScore'] ?? null),
                        'sets' => collect($jogo['sets'] ?? [])
                            ->filter(fn ($set) => ($set['pointsTeamA'] ?? 0) > 0 || ($set['pointsTeamB'] ?? 0) > 0)
                            ->values()
                            ->all(),
                        'status' => $this->statusPartida((int) ($jogo['matchStatus'] ?? 0), $jogo),
                        'source_url' => $this->absoluteUrl($jogo['matchCenterUrl'] ?? '/volleyball/competitions/volleyball-nations-league/schedule/'),
                        'origem' => 'scraping',
                        'importado_em' => now(),
                    ]
                );
            }
        });

        return [
            'partidas' => count($payload['matches']),
            'selecoes' => count($selecoes),
            'source_url' => $url,
        ];
    }

    private function generosPorTime(array $payload): array
    {
        $generos = [];

        foreach ($payload['matches'] ?? [] as $jogo) {
            $genero = $this->normalizarGenero($jogo['gender'] ?? null);

            if (! $genero) {
                continue;
            }

            foreach (['teamANo', 'teamBNo'] as $campo) {
                $numero = (string) ($jogo[$campo] ?? '');

                if ($numero !== '') {
                    $generos[$numero][$genero] = $genero;
                }
            }
        }

        foreach ($payload['allTeams'] ?? [] as $time) {
            $genero = $this->normalizarGenero($time['gender'] ?? $time['competitionGender'] ?? null);
            $numero = (string) ($time['no'] ?? '');

            if ($numero !== '' && $genero) {
                $generos[$numero][$genero] = $genero;
            }
        }

        return array_map('array_values', $generos);
    }

    public function importarClassificacao(string $genero): array
    {
        $segmento = $genero === 'feminino' ? 'women' : 'men';
        $url = "{$this->baseUrl()}/volleyball/competitions/volleyball-nations-league/standings/{$segmento}/";
        $html = $this->get($url)->body();
        $linhas = $this->parseClassificacao($html);

        if ($linhas === []) {
            throw new RuntimeException("Nenhuma linha de classificação {$genero} foi encontrada.");
        }

        DB::transaction(function () use ($linhas, $genero, $url) {
            foreach ($linhas as $linha) {
                $selecao = Selecao::updateOrCreate(
                    [
                        'sigla' => $linha['sigla'],
                        'genero' => $genero,
                    ],
                    [
                        'nome' => $this->nomeSelecaoPtBr($linha['nome'], $linha['sigla']),
                        'bandeira' => $linha['bandeira'],
                        'source_url' => $url,
                        'ativo' => true,
                    ]
                );

                Classificacao::updateOrCreate(
                    [
                        'selecao_id' => $selecao->id,
                        'genero' => $genero,
                        'temporada' => config('services.volleyball_world.season'),
                    ],
                    [
                        ...$linha,
                        'selecao_id' => $selecao->id,
                        'genero' => $genero,
                        'temporada' => config('services.volleyball_world.season'),
                        'origem' => 'scraping',
                        'source_url' => $url,
                        'importado_em' => now(),
                    ]
                );
            }
        });

        return ['linhas' => count($linhas), 'source_url' => $url];
    }

    public function atualizarJogadores(): ScrapingLog
    {
        $log = ScrapingLog::create([
            'tipo' => 'vnl_jogadores',
            'status' => 'executando',
            'source_url' => $this->baseUrl(),
            'iniciado_em' => now(),
        ]);

        try {
            $detalhes = $this->importarJogadores();

            $log->update([
                'status' => 'sucesso',
                'mensagem' => 'Jogadores da VNL atualizados com sucesso.',
                'detalhes' => $detalhes,
                'registros_processados' => $detalhes['jogadores'],
                'finalizado_em' => now(),
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'erro',
                'mensagem' => $exception->getMessage(),
                'detalhes' => ['exception' => $exception::class],
                'registros_processados' => 0,
                'finalizado_em' => now(),
            ]);
        }

        return $log->fresh();
    }

    public function importarJogadores(): array
    {
        $jogadores = 0;
        $selecoes = 0;
        $sourceUrls = [];

        foreach (['masculino' => 'men', 'feminino' => 'women'] as $genero => $segmento) {
            $url = "{$this->baseUrl()}/volleyball/competitions/volleyball-nations-league/teams/{$segmento}/";
            $sourceUrls[] = $url;
            $times = $this->parseLinksTimes($this->get($url)->body(), $segmento);

            foreach ($times as $time) {
                $selecao = Selecao::updateOrCreate(
                    [
                        'sigla' => $time['sigla'],
                        'genero' => $genero,
                    ],
                    [
                        'nome' => $this->nomeSelecaoPtBr($time['nome'], $time['sigla']),
                        'source_url' => $time['url'],
                        'ativo' => true,
                    ]
                );

                $selecoes++;
                $playersUrl = str_replace('/schedule/', '/players/', $time['url']);
                $sourceUrls[] = $playersUrl;

                foreach ($this->parseJogadoresHtml($this->get($playersUrl)->body()) as $jogador) {
                    $posicao = $this->posicaoPorCodigo($jogador['posicao']);

                    Jogador::updateOrCreate(
                        [
                            'selecao_id' => $selecao->id,
                            'nome' => $jogador['nome'],
                        ],
                        [
                            'posicao_id' => $posicao->id,
                            'genero' => $genero,
                            'valor_creditos' => 10,
                            'media_pontos' => 0,
                            'ativo' => true,
                        ]
                    );

                    $jogadores++;
                }
            }
        }

        return [
            'selecoes' => $selecoes,
            'jogadores' => $jogadores,
            'source_urls' => array_values(array_unique($sourceUrls)),
        ];
    }

    public function parseLinksTimes(string $html, string $segmento): array
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($html);
        $xpath = new DOMXPath($document);
        $links = $xpath->query("//a[contains(@href, '/teams/{$segmento}/') and contains(@href, '/schedule/')]");
        $times = [];

        foreach ($links as $link) {
            if (! $link instanceof DOMElement) {
                continue;
            }

            $texto = trim(preg_replace('/\s+/', ' ', $link->textContent));

            if (! preg_match('/^(.+?)\s+([A-Z]{2,3})$/', $texto, $matches)) {
                continue;
            }

            $times[$matches[2]] = [
                'nome' => $matches[1],
                'sigla' => $matches[2],
                'url' => $this->absoluteUrl($link->getAttribute('href')),
            ];
        }

        libxml_clear_errors();

        return array_values($times);
    }

    public function parseJogadoresHtml(string $html): array
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($html);
        $xpath = new DOMXPath($document);
        $anchors = $xpath->query('//a[@href]');
        $grupos = [];

        foreach ($anchors as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $href = $anchor->getAttribute('href');
            $texto = trim(preg_replace('/\s+/', ' ', $anchor->textContent));

            if ($href === '' || $texto === '') {
                continue;
            }

            $ultimo = array_key_last($grupos);

            if ($ultimo !== null && $grupos[$ultimo]['href'] === $href) {
                $grupos[$ultimo]['textos'][] = $texto;
                continue;
            }

            $grupos[] = ['href' => $href, 'textos' => [$texto]];
        }

        $jogadores = [];

        foreach ($grupos as $grupo) {
            $textos = $grupo['textos'];

            if (count($textos) < 3 || ! ctype_digit($textos[0])) {
                continue;
            }

            $posicao = strtoupper(end($textos));

            if (! $this->codigoPosicaoConhecido($posicao)) {
                continue;
            }

            $jogadores[] = [
                'nome' => $textos[1],
                'posicao' => $posicao,
            ];
        }

        libxml_clear_errors();

        return $jogadores;
    }

    public function parseClassificacao(string $html): array
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $document->loadHTML($html);
        $xpath = new DOMXPath($document);
        $rows = $xpath->query("//tr[contains(@class, 'vbw-o-table__row')]");
        $resultado = [];

        foreach ($rows as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }

            $nome = $this->xpathText($xpath, ".//td[contains(@class,'team')]//div[contains(@class,'vbw-mu__team__name') and not(contains(@class,'abbr'))]//a", $row);
            $sigla = $this->xpathText($xpath, ".//td[contains(@class,'team')]//div[contains(@class,'abbr')]//a", $row);

            if ($nome === '' || $sigla === '') {
                continue;
            }

            $resultado[] = [
                'nome' => $nome,
                'sigla' => $sigla,
                'bandeira' => $this->xpathAttribute($xpath, ".//td[contains(@class,'team')]//img", 'src', $row),
                'posicao' => $this->intCell($xpath, $row, 'position'),
                'jogos' => $this->intCell($xpath, $row, 'matchestotal'),
                'vitorias' => $this->intCell($xpath, $row, 'matcheswon'),
                'derrotas' => $this->intCell($xpath, $row, 'matcheslost'),
                'pontos' => $this->intCell($xpath, $row, 'matchpoints'),
                'sets_pro' => $this->intCell($xpath, $row, 'setswon'),
                'sets_contra' => $this->intCell($xpath, $row, 'setslost'),
                'set_ratio' => $this->decimalCell($xpath, $row, 'setsratio'),
                'pontos_pro' => $this->intCell($xpath, $row, 'pointswon'),
                'pontos_contra' => $this->intCell($xpath, $row, 'pointslost'),
                'ponto_ratio' => $this->decimalCell($xpath, $row, 'pointsratio'),
            ];
        }

        libxml_clear_errors();

        return $resultado;
    }

    private function salvarSelecao(array $time, string $genero, string $sourceUrl): Selecao
    {
        return Selecao::updateOrCreate(
            [
                'external_ref' => (string) $time['no'],
                'genero' => $genero,
            ],
            [
                'nome' => $this->nomeSelecaoPtBr(($time['translatedName'] ?? null) ?: $time['name'], $time['code'] ?? null),
                'sigla' => ($time['code'] ?? null) ?: null,
                'bandeira' => $time['img'] ?? null,
                'source_url' => $sourceUrl,
                'ativo' => true,
            ]
        );
    }

    private function nomeSelecaoPtBr(string $nome, mixed $sigla = null): string
    {
        $porSigla = [
            'ARG' => 'Argentina',
            'BEL' => 'Bélgica',
            'BRA' => 'Brasil',
            'BUL' => 'Bulgária',
            'CAN' => 'Canadá',
            'CHN' => 'China',
            'COL' => 'Colômbia',
            'CRO' => 'Croácia',
            'CUB' => 'Cuba',
            'CZE' => 'Tchéquia',
            'DOM' => 'República Dominicana',
            'FRA' => 'França',
            'GER' => 'Alemanha',
            'GRE' => 'Grécia',
            'IRI' => 'Irã',
            'ITA' => 'Itália',
            'JPN' => 'Japão',
            'KOR' => 'Coreia do Sul',
            'NED' => 'Países Baixos',
            'POL' => 'Polônia',
            'PUR' => 'Porto Rico',
            'SRB' => 'Sérvia',
            'SLO' => 'Eslovênia',
            'THA' => 'Tailândia',
            'TUR' => 'Turquia',
            'UKR' => 'Ucrânia',
            'USA' => 'Estados Unidos',
        ];

        $sigla = strtoupper((string) $sigla);

        if ($sigla !== '' && isset($porSigla[$sigla])) {
            return $porSigla[$sigla];
        }

        $porNome = [
            'belgium' => 'Bélgica',
            'brazil' => 'Brasil',
            'bulgaria' => 'Bulgária',
            'canada' => 'Canadá',
            'colombia' => 'Colômbia',
            'croatia' => 'Croácia',
            'czech republic' => 'Tchéquia',
            'czechia' => 'Tchéquia',
            'dominican republic' => 'República Dominicana',
            'france' => 'França',
            'germany' => 'Alemanha',
            'greece' => 'Grécia',
            'iran' => 'Irã',
            'italy' => 'Itália',
            'japan' => 'Japão',
            'korea' => 'Coreia do Sul',
            'netherlands' => 'Países Baixos',
            'poland' => 'Polônia',
            'puerto rico' => 'Porto Rico',
            'serbia' => 'Sérvia',
            'slovenia' => 'Eslovênia',
            'south korea' => 'Coreia do Sul',
            'thailand' => 'Tailândia',
            'turkey' => 'Turquia',
            'türkiye' => 'Turquia',
            'ukraine' => 'Ucrânia',
            'united states' => 'Estados Unidos',
            'united states of america' => 'Estados Unidos',
            'usa' => 'Estados Unidos',
        ];

        return $porNome[strtolower($nome)] ?? $nome;
    }

    private function posicaoPorCodigo(string $codigo): Posicao
    {
        $mapa = [
            'S' => ['sigla' => 'LEV', 'nome' => 'Levantador'],
            'SETTER' => ['sigla' => 'LEV', 'nome' => 'Levantador'],
            'O' => ['sigla' => 'OPO', 'nome' => 'Oposto'],
            'OP' => ['sigla' => 'OPO', 'nome' => 'Oposto'],
            'OPPOSITE' => ['sigla' => 'OPO', 'nome' => 'Oposto'],
            'OH' => ['sigla' => 'PON', 'nome' => 'Ponteiro'],
            'OUTSIDE HITTER' => ['sigla' => 'PON', 'nome' => 'Ponteiro'],
            'MB' => ['sigla' => 'CEN', 'nome' => 'Central'],
            'MIDDLE BLOCKER' => ['sigla' => 'CEN', 'nome' => 'Central'],
            'L' => ['sigla' => 'LIB', 'nome' => 'Líbero'],
            'LIBERO' => ['sigla' => 'LIB', 'nome' => 'Líbero'],
        ];

        $posicao = $mapa[strtoupper($codigo)] ?? ['sigla' => 'PON', 'nome' => 'Ponteiro'];

        return Posicao::firstOrCreate(['sigla' => $posicao['sigla']], ['nome' => $posicao['nome']]);
    }

    private function codigoPosicaoConhecido(string $codigo): bool
    {
        return in_array(strtoupper($codigo), ['S', 'SETTER', 'O', 'OP', 'OPPOSITE', 'OH', 'OUTSIDE HITTER', 'MB', 'MIDDLE BLOCKER', 'L', 'LIBERO'], true);
    }

    private function timeIndefinido(array $time): bool
    {
        return strtoupper((string) ($time['name'] ?? '')) === 'TBD'
            || strtoupper((string) ($time['translatedName'] ?? '')) === 'TBD';
    }

    private function normalizarGenero(mixed $genero): ?string
    {
        return match (strtolower((string) $genero)) {
            'women', 'woman', 'female', 'feminino' => 'feminino',
            'men', 'man', 'male', 'masculino' => 'masculino',
            default => null,
        };
    }

    private function statusPartida(int $status, array $jogo): string
    {
        if ($status === 2) {
            return 'encerrado';
        }

        if ($status === 1) {
            return 'ao_vivo';
        }

        if (($jogo['isMatchTBD'] ?? false) === true) {
            return 'a_definir';
        }

        return 'agendado';
    }

    private function normalizarPlacar(mixed $placar): ?int
    {
        if (! is_numeric($placar)) {
            return null;
        }

        $placar = (int) $placar;

        return $placar >= 0 ? $placar : null;
    }

    private function get(string $url)
    {
        return $this->http
            ->withHeaders([
                'User-Agent' => 'FantasyVolei/1.0 (+dados públicos da Volleyball World)',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
            ->timeout(30)
            ->retry(2, 500)
            ->get($url)
            ->throw();
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.volleyball_world.base_url'), '/');
    }

    private function absoluteUrl(string $url): string
    {
        return str_starts_with($url, 'http') ? $url : $this->baseUrl().'/'.ltrim($url, '/');
    }

    private function intCell(DOMXPath $xpath, DOMElement $row, string $class): int
    {
        return (int) $this->xpathText($xpath, ".//td[contains(@class,'{$class}')]", $row);
    }

    private function decimalCell(DOMXPath $xpath, DOMElement $row, string $class): ?float
    {
        $value = $this->xpathText($xpath, ".//td[contains(@class,'{$class}')]", $row);

        return is_numeric($value) ? (float) $value : null;
    }

    private function xpathText(DOMXPath $xpath, string $query, DOMElement $context): string
    {
        return trim($xpath->query($query, $context)?->item(0)?->textContent ?? '');
    }

    private function xpathAttribute(DOMXPath $xpath, string $query, string $attribute, DOMElement $context): ?string
    {
        $value = $xpath->query($query, $context)?->item(0)?->attributes?->getNamedItem($attribute)?->nodeValue;

        return $value ? trim($value) : null;
    }
}
