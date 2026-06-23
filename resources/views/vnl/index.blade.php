<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">Volleyball Nations League</p>
                <h2 class="mt-1 text-2xl font-extrabold text-slate-950">VNL {{ $temporada }}</h2>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                <a href="{{ route('vnl.index', ['genero' => 'masculino']) }}" class="rounded-lg px-4 py-2 text-sm font-bold {{ $genero === 'masculino' ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Masculino
                </a>
                <a href="{{ route('vnl.index', ['genero' => 'feminino']) }}" class="rounded-lg px-4 py-2 text-sm font-bold {{ $genero === 'feminino' ? 'bg-blue-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Feminino
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if ($erro)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                    <p class="font-bold">Não foi possível carregar os dados da VNL.</p>
                    <p class="mt-1 text-sm">{{ $erro }}</p>
                </div>
            @endif

            <section>
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-950">Placares e jogos</h3>
                        <p class="mt-1 text-sm text-slate-500">Resultados e próximos confrontos da competição.</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-500">{{ $jogos->count() }} jogos</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($jogos as $jogo)
                        @php
                            $casa = data_get($jogo, 'teams.home', []);
                            $fora = data_get($jogo, 'teams.away', []);
                            $placarCasa = data_get($jogo, 'scores.home');
                            $placarFora = data_get($jogo, 'scores.away');
                            $status = data_get($jogo, 'status.long', data_get($jogo, 'status', 'Agendado'));
                            $data = data_get($jogo, 'date');
                        @endphp
                        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                            <div class="mb-5 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500">
                                <span>{{ $data ? \Illuminate\Support\Carbon::parse($data)->timezone(config('app.timezone'))->format('d/m · H:i') : 'Data a definir' }}</span>
                                <span class="{{ str_contains(strtolower((string) $status), 'live') ? 'text-red-600' : 'text-blue-700' }}">{{ $status }}</span>
                            </div>

                            <div class="space-y-4">
                                @foreach ([[$casa, $placarCasa], [$fora, $placarFora]] as [$time, $placar])
                                    <div class="flex items-center gap-3">
                                        @if (data_get($time, 'logo'))
                                            <img src="{{ data_get($time, 'logo') }}" alt="" class="h-10 w-10 rounded-full object-contain">
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-50">🏐</div>
                                        @endif
                                        <span class="min-w-0 flex-1 truncate font-bold text-slate-900">{{ data_get($time, 'name', 'A definir') }}</span>
                                        <span class="text-2xl font-extrabold text-slate-950">{{ $placar ?? '–' }}</span>
                                    </div>
                                @endforeach
                            </div>

                            @if (data_get($jogo, 'week'))
                                <div class="mt-5 border-t border-slate-100 pt-4 text-xs font-semibold text-slate-500">{{ data_get($jogo, 'week') }}</div>
                            @endif
                        </article>
                    @empty
                        <div class="md:col-span-2 xl:col-span-3 rounded-2xl bg-white px-6 py-12 text-center text-slate-500 shadow-sm ring-1 ring-slate-200">
                            Nenhum jogo encontrado para esta temporada.
                        </div>
                    @endforelse
                </div>
            </section>

            <section>
                <div class="mb-4">
                    <h3 class="text-xl font-extrabold text-slate-950">Classificação</h3>
                    <p class="mt-1 text-sm text-slate-500">Tabela atualizada da fase classificatória.</p>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">Seleção</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-500">J</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-500">V</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-500">D</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-500">Sets</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-500">Pts</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($classificacao as $linha)
                                    @php
                                        $time = data_get($linha, 'team', []);
                                        $jogos = data_get($linha, 'games', []);
                                        $sets = data_get($linha, 'sets', []);
                                    @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-4 text-sm font-extrabold text-blue-700">{{ data_get($linha, 'position', data_get($linha, 'rank', '–')) }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                @if (data_get($time, 'logo'))
                                                    <img src="{{ data_get($time, 'logo') }}" alt="" class="h-8 w-8 object-contain">
                                                @endif
                                                <span class="whitespace-nowrap font-bold text-slate-900">{{ data_get($time, 'name', data_get($linha, 'team.name', 'Seleção')) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm text-slate-600">{{ data_get($jogos, 'played', data_get($linha, 'games_played', 0)) }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-bold text-emerald-700">{{ data_get($jogos, 'win.total', data_get($jogos, 'won', data_get($linha, 'wins', 0))) }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-bold text-red-600">{{ data_get($jogos, 'lose.total', data_get($jogos, 'lost', data_get($linha, 'losses', 0))) }}</td>
                                        <td class="px-4 py-4 text-center text-sm text-slate-600">{{ data_get($sets, 'for', 0) }}:{{ data_get($sets, 'against', 0) }}</td>
                                        <td class="px-4 py-4 text-center text-base font-extrabold text-slate-950">{{ data_get($linha, 'points', 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">Classificação indisponível.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
