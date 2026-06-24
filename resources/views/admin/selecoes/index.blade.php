<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Seleções</h2>
                <p class="mt-1 text-sm text-slate-500">Gerencie as seleções disponíveis no fantasy.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('admin.scraping.atualizar') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerText = 'Atualizando...';">
                    @csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                        Atualizar da Volleyball World
                    </button>
                </form>
                <a href="{{ route('admin.selecoes.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-blue-800">
                    Nova seleção
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <form method="GET" action="{{ route('admin.selecoes.index') }}" class="mb-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                <div class="grid gap-4 md:grid-cols-[1fr_220px_auto_auto] md:items-end">
                    <div>
                        <label for="selecao" class="text-sm font-bold text-slate-700">Seleção</label>
                        <input id="selecao" name="selecao" type="search" value="{{ $filtros['selecao'] ?? '' }}" placeholder="Nome ou sigla" class="mt-2 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="genero" class="text-sm font-bold text-slate-700">Gênero</label>
                        <select id="genero" name="genero" class="mt-2 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="masculino" @selected(($filtros['genero'] ?? '') === 'masculino')>Masculino</option>
                            <option value="feminino" @selected(($filtros['genero'] ?? '') === 'feminino')>Feminino</option>
                        </select>
                    </div>

                    <button class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800">
                        Filtrar
                    </button>

                    <a href="{{ route('admin.selecoes.index') }}" class="rounded-lg bg-slate-100 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-200">
                        Limpar
                    </a>
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Seleção</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Gênero</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jogadores</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($selecoes as $selecao)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($selecao->bandeira)
                                                <img src="{{ $selecao->bandeira }}" alt="" class="h-9 w-12 rounded object-cover">
                                            @else
                                                <span class="flex h-9 w-12 items-center justify-center rounded bg-blue-50 text-sm font-bold text-blue-700">
                                                    {{ $selecao->sigla ?: 'VNL' }}
                                                </span>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $selecao->nome }}</div>
                                                <div class="text-xs text-slate-500">{{ $selecao->sigla ?: 'Sem sigla' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm capitalize text-slate-600">{{ $selecao->genero }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $selecao->jogadores_count }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $selecao->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $selecao->ativo ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('admin.selecoes.show', $selecao) }}" class="font-semibold text-blue-700 hover:text-blue-900">Ver</a>
                                        <a href="{{ route('admin.selecoes.edit', $selecao) }}" class="ml-4 font-semibold text-amber-600 hover:text-amber-800">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">Nenhuma seleção encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($selecoes->hasPages())
                    <div class="border-t border-slate-200 px-6 py-4">{{ $selecoes->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
