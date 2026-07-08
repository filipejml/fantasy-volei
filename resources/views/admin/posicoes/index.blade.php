<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Posicoes</h2>
                <p class="mt-1 text-sm text-slate-500">Resumo de atletas por funcao em quadra.</p>
            </div>
            <a href="{{ route('admin.posicoes.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-blue-800">
                Nova posicao
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($posicoes as $posicao)
                    <article class="flex min-h-[320px] flex-col rounded-lg bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-extrabold uppercase text-blue-700 ring-1 ring-blue-100">
                                    {{ $posicao->sigla }}
                                </span>
                                <h3 class="mt-3 text-lg font-extrabold text-slate-900">{{ $posicao->nome }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $posicao->jogadores_count }} jogadores cadastrados</p>
                            </div>

                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('admin.jogadores.index', ['posicao_id' => $posicao->id]) }}" class="font-bold text-slate-700 hover:text-slate-900">Ver</a>
                                <a href="{{ route('admin.posicoes.edit', $posicao) }}" class="font-bold text-blue-700 hover:text-blue-900">Editar</a>
                                <form method="POST" action="{{ route('admin.posicoes.destroy', $posicao) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="font-bold text-red-600 hover:text-red-800">Excluir</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-5 flex-1">
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Pre-lista</h4>
                                <a href="{{ route('admin.jogadores.index', ['posicao_id' => $posicao->id]) }}" class="text-xs font-bold text-blue-700 hover:text-blue-900">
                                    Ver todos
                                </a>
                            </div>

                            <div class="space-y-2.5">
                                @forelse($posicao->jogadores as $jogador)
                                    <a href="{{ route('admin.jogadores.show', $jogador) }}" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 transition hover:border-blue-200 hover:bg-blue-50/60">
                                        @if($jogador->foto)
                                            <img src="{{ $jogador->foto }}" alt="" class="h-9 w-9 rounded-full object-cover">
                                        @else
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-extrabold text-blue-700">
                                                {{ mb_substr($jogador->nome, 0, 1) }}
                                            </span>
                                        @endif

                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-bold text-slate-900">{{ $jogador->nome }}</span>
                                            <span class="block truncate text-xs text-slate-500">
                                                {{ $jogador->selecao?->nome ?? 'Sem selecao' }} - {{ ucfirst($jogador->genero) }}
                                            </span>
                                        </span>

                                        <span class="text-xs font-bold text-blue-700">C$ {{ number_format($jogador->valor_creditos, 2, ',', '.') }}</span>
                                    </a>
                                @empty
                                    <div class="rounded-lg border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">
                                        Nenhum jogador ativo nesta posicao.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                        Nenhuma posicao cadastrada.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
