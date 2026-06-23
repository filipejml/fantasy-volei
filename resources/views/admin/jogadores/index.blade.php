<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Jogadores</h2>
                <p class="mt-1 text-sm text-slate-500">Gerencie atletas, posições e valores.</p>
            </div>
            <a href="{{ route('admin.jogadores.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-800">Novo jogador</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <form method="GET" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:grid-cols-[1fr_240px_auto]">
                <input name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome..." class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                <select name="selecao_id" class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas as seleções</option>
                    @foreach ($selecoes as $selecao)
                        <option value="{{ $selecao->id }}" @selected((string) request('selecao_id') === (string) $selecao->id)>{{ $selecao->nome }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filtrar</button>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Jogador</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Seleção</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Posição</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($jogadores as $jogador)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($jogador->foto)
                                                <img src="{{ $jogador->foto }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                            @else
                                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">{{ mb_substr($jogador->nome, 0, 1) }}</span>
                                            @endif
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $jogador->nome }}</div>
                                                <div class="text-xs capitalize text-slate-500">{{ $jogador->genero }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $jogador->selecao->nome }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $jogador->posicao->sigla }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-700">C$ {{ number_format($jogador->valor_creditos, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $jogador->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $jogador->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('admin.jogadores.show', $jogador) }}" class="font-semibold text-blue-700 hover:text-blue-900">Ver</a>
                                        <a href="{{ route('admin.jogadores.edit', $jogador) }}" class="ml-4 font-semibold text-amber-600 hover:text-amber-800">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">Nenhum jogador encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($jogadores->hasPages())
                    <div class="border-t border-slate-200 px-6 py-4">{{ $jogadores->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
