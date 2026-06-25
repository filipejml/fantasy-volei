<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Meu time</h2>
                <p class="mt-1 text-sm text-slate-500">Monte seus elencos fantasy da VNL e acompanhe orçamento e pontuação.</p>
            </div>
            <a href="{{ route('times.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-blue-800">
                Montar time
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            @if($times->isEmpty())
                <section class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-slate-200">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">VNL Fantasy</p>
                    <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Você ainda não montou um time</h1>
                    <p class="mx-auto mt-3 max-w-2xl text-slate-500">Escolha 6 titulares e 6 reservas, respeite seu limite de créditos e salve seu elenco para acompanhar durante a competição.</p>
                    <a href="{{ route('times.create') }}" class="mt-6 inline-flex rounded-lg bg-blue-700 px-5 py-3 text-sm font-bold text-white hover:bg-blue-800">
                        Começar agora
                    </a>
                </section>
            @else
                <div class="grid gap-6 lg:grid-cols-2">
                    @foreach($times as $time)
                        @php
                            $limite = (float) $time->creditos_limite;
                            $usados = (float) $time->creditos_usados;
                            $percentual = $limite > 0 ? min(100, ($usados / $limite) * 100) : 0;
                        @endphp

                        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ ucfirst($time->genero) }}</p>
                                    <h3 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $time->nome }}</h3>
                                </div>
                                <a href="{{ route('times.edit', $time) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-200">
                                    Editar
                                </a>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-xl bg-blue-50 p-4">
                                    <p class="text-xs font-bold uppercase text-blue-700">Jogadores</p>
                                    <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $time->jogadores->count() }}/12</p>
                                </div>
                                <div class="rounded-xl bg-emerald-50 p-4">
                                    <p class="text-xs font-bold uppercase text-emerald-700">Créditos</p>
                                    <p class="mt-1 text-2xl font-extrabold text-slate-900">C$ {{ number_format($usados, 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-xl bg-amber-50 p-4">
                                    <p class="text-xs font-bold uppercase text-amber-700">Pontos</p>
                                    <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format((float) $time->pontuacao_total, 1, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="mb-2 flex justify-between text-xs font-bold text-slate-500">
                                    <span>Orçamento usado</span>
                                    <span>C$ {{ number_format($usados, 2, ',', '.') }} / {{ number_format($limite, 2, ',', '.') }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-blue-700" style="width: {{ $percentual }}%"></div>
                                </div>
                            </div>

                            <div class="mt-6 divide-y divide-slate-100">
                                @forelse($time->jogadores->sortBy(fn ($jogador) => [$jogador->posicao->sigla, $jogador->nome]) as $jogador)
                                    <div class="flex items-center justify-between gap-3 py-3">
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $jogador->nome }}</p>
                                            <p class="text-sm text-slate-500">{{ $jogador->posicao->sigla }} · {{ $jogador->selecao->nome }}</p>
                                        </div>
                                        <span class="text-sm font-bold text-blue-700">C$ {{ number_format((float) $jogador->valor_creditos, 2, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <p class="py-6 text-center text-sm text-slate-500">Nenhum jogador selecionado.</p>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
