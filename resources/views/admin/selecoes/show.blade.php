<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-slate-900">Detalhes da seleção</h2>
            <a href="{{ route('admin.selecoes.edit', $selecao) }}" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600">Editar</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                    @if ($selecao->bandeira)
                        <img src="{{ $selecao->bandeira }}" alt="Bandeira de {{ $selecao->nome }}" class="h-28 w-40 rounded-xl object-cover ring-1 ring-slate-200">
                    @else
                        <div class="flex h-28 w-40 items-center justify-center rounded-xl bg-blue-50 text-5xl">🏐</div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-extrabold text-slate-950">{{ $selecao->nome }}</h1>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $selecao->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $selecao->ativo ? 'Ativa' : 'Inativa' }}</span>
                        </div>
                        <p class="mt-2 capitalize text-slate-500">{{ $selecao->genero }} · {{ $selecao->sigla ?: 'Sem sigla' }}</p>
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Jogadores ({{ $selecao->jogadores->count() }})</h3>
                        <a href="{{ route('admin.jogadores.create', ['selecao_id' => $selecao->id]) }}" class="text-sm font-bold text-blue-700 hover:text-blue-900">Adicionar jogador</a>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse ($selecao->jogadores as $jogador)
                            <a href="{{ route('admin.jogadores.show', $jogador) }}" class="flex items-center justify-between rounded-xl border border-slate-200 p-4 hover:border-blue-300 hover:bg-blue-50/50">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $jogador->nome }}</div>
                                    <div class="text-sm text-slate-500">{{ $jogador->posicao->nome }}</div>
                                </div>
                                <span class="font-bold text-blue-700">C$ {{ number_format($jogador->valor_creditos, 2, ',', '.') }}</span>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">Nenhum jogador vinculado.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-6">
                    <a href="{{ route('admin.selecoes.index') }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">← Voltar</a>
                    <form method="POST" action="{{ route('admin.selecoes.destroy', $selecao) }}" onsubmit="return confirm('Excluir esta seleção e todos os jogadores vinculados?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-bold text-red-600 hover:text-red-800">Excluir seleção</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
