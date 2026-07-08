<x-app-layout>
    @php
        $temJogoAoVivo = $jogos->contains(fn ($jogo) => $jogo->status === 'ao_vivo');
        $jogosAoVivo = $jogos->where('status', 'ao_vivo');
        $jogosFuturos = $jogos->reject(fn ($jogo) => in_array($jogo->status, ['ao_vivo', 'encerrado'], true));
        $jogosFinalizados = $jogos->where('status', 'encerrado')->sortByDesc('data_partida');
        $gruposDeJogos = [
            ['titulo' => 'Ao vivo', 'descricao' => 'Partidas em andamento agora.', 'jogos' => $jogosAoVivo, 'mostrar' => $jogosAoVivo->isNotEmpty(), 'aberto' => $jogosAoVivo->isNotEmpty()],
            ['titulo' => 'Próximos jogos', 'descricao' => 'Partidas que ainda não ocorreram.', 'jogos' => $jogosFuturos, 'mostrar' => true, 'aberto' => false],
            ['titulo' => 'Finalizados', 'descricao' => 'Resultados já encerrados.', 'jogos' => $jogosFinalizados, 'mostrar' => true, 'aberto' => false],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-blue-700">Volleyball Nations League</p>
                <h2 class="mt-1 text-2xl font-extrabold">VNL {{ $temporada }}</h2>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                @foreach(['masculino' => 'Masculino', 'feminino' => 'Feminino'] as $valor => $label)
                    <a href="{{ route('vnl.index', ['genero' => $valor, 'temporada' => $temporada]) }}" class="rounded-lg px-4 py-2 text-sm font-bold {{ $genero === $valor ? 'bg-blue-700 text-white' : 'text-slate-600' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="space-y-7">
                <div class="mb-4">
                    <h3 class="text-xl font-extrabold">Jogos e resultados</h3>
                    <p class="text-sm text-slate-500">Dados persistidos no sistema, importados da Volleyball World ou corrigidos manualmente.</p>
                </div>

                @if($jogos->isEmpty())
                    <div class="rounded-2xl bg-white p-10 text-center text-slate-500">
                        Nenhuma partida cadastrada para esta categoria.
                    </div>
                @endif

                @foreach($gruposDeJogos as $grupo)
                    @continue(! $grupo['mostrar'])

                    <details class="group rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200" @if($grupo['aberto']) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
                            <div>
                                <h4 class="text-lg font-extrabold text-slate-900">{{ $grupo['titulo'] }}</h4>
                                <p class="text-sm text-slate-500">{{ $grupo['descricao'] }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-600">{{ $grupo['jogos']->count() }}</span>
                                <span class="text-xl font-bold text-slate-400 transition group-open:rotate-180">⌄</span>
                            </div>
                        </summary>

                        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse($grupo['jogos'] as $jogo)
                                @include('vnl._jogo-card', ['jogo' => $jogo])
                            @empty
                                <div class="rounded-2xl bg-white p-10 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                                    Nenhuma partida nesta seção.
                                </div>
                            @endforelse
                        </div>
                    </details>
                @endforeach
            </section>
        </div>
    </div>

    @if($temJogoAoVivo)
        <script>
            setTimeout(() => window.location.reload(), 30000);
        </script>
    @endif
</x-app-layout>
