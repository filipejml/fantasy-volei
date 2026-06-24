<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $temJogoAoVivo = $jogosHoje->contains(fn ($jogo) => $jogo->status === 'ao_vivo');
            @endphp

            <section class="mb-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">VNL</p>
                        <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Jogos de hoje</h1>
                    </div>
                    <a href="{{ route('vnl.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-800">
                        Ver VNL
                    </a>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($jogosHoje as $jogo)
                        @php
                            $sets = collect($jogo->sets ?? []);
                            $ultimoSet = $sets->last();
                            $pontosCasa = data_get($ultimoSet, 'pointsTeamA');
                            $pontosFora = data_get($ultimoSet, 'pointsTeamB');
                            $temPontosAoVivo = $jogo->status === 'ao_vivo' && ($pontosCasa || $pontosFora);
                        @endphp

                        <article class="rounded-xl border {{ $jogo->status === 'ao_vivo' ? 'border-red-200 bg-red-50/40' : 'border-slate-200' }} p-4">
                            <div class="mb-4 flex items-center justify-between gap-3 text-xs font-bold uppercase text-slate-500">
                                <span>{{ $jogo->data_partida->format('H:i') }}</span>
                                <div class="flex items-center gap-2">
                                    @if($jogo->status === 'ao_vivo')
                                        <span class="rounded-full bg-red-600 px-3 py-1 text-white">Ao vivo</span>
                                    @endif
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ ucfirst($jogo->genero) }}</span>
                                </div>
                            </div>

                            @foreach([[$jogo->selecaoCasa, $jogo->placar_casa], [$jogo->selecaoFora, $jogo->placar_fora]] as [$selecao, $placar])
                                <div class="mb-3 flex items-center gap-3">
                                    @if($selecao?->bandeira)
                                        <img src="{{ $selecao->bandeira }}" class="h-8 w-10 object-contain" alt="">
                                    @else
                                        <span class="flex h-8 w-10 items-center justify-center rounded bg-blue-50 text-sm font-bold text-blue-700">
                                            {{ $selecao?->sigla ?? 'VNL' }}
                                        </span>
                                    @endif
                                    <span class="min-w-0 flex-1 truncate font-bold text-slate-900">{{ $selecao?->nome ?? 'Selecao' }}</span>
                                    <strong class="text-xl text-slate-900">{{ $placar !== null && $placar >= 0 ? $placar : 0 }}</strong>
                                </div>
                            @endforeach

                            @if($temPontosAoVivo)
                                <div class="mt-4 rounded-lg bg-white px-3 py-2 text-sm font-bold text-red-700 ring-1 ring-red-100">
                                    Set atual: {{ $pontosCasa }} x {{ $pontosFora }}
                                </div>
                            @elseif($sets->isNotEmpty())
                                <div class="mt-4 text-xs text-slate-500">
                                    Parciais:
                                    {{ $sets->map(fn ($set) => data_get($set, 'pointsTeamA', 0).'x'.data_get($set, 'pointsTeamB', 0))->join(', ') }}
                                </div>
                            @endif

                            <div class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                {{ collect([$jogo->rodada, $jogo->local, str($jogo->status)->replace('_', ' ')->title()])->filter()->join(' - ') }}
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                            Nenhum jogo da VNL cadastrado para hoje.
                        </div>
                    @endforelse
                </div>
            </section>

            @unless (Auth::user()->isAdmin())
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-gray-900">Você está conectado!</div>
                </div>
            @endunless
        </div>
    </div>

    @if($temJogoAoVivo)
        <script>
            setTimeout(() => window.location.reload(), 30000);
        </script>
    @endif
</x-app-layout>
