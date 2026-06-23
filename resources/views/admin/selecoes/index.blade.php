<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Seleções</h2>
                <p class="mt-1 text-sm text-slate-500">Gerencie as seleções disponíveis no fantasy.</p>
            </div>
            <a href="{{ route('admin.selecoes.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800">
                Nova seleção
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

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
                                                <span class="flex h-9 w-12 items-center justify-center rounded bg-blue-50 text-lg">🏐</span>
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
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">Nenhuma seleção cadastrada.</td>
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
