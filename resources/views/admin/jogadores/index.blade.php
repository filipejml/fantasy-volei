<x-app-layout>
    @php
        $sortUrl = function (string $campo) use ($ordenarPor, $direcao) {
            $novaDirecao = $ordenarPor === $campo && $direcao === 'asc' ? 'desc' : 'asc';

            return route('admin.jogadores.index', array_merge(request()->query(), [
                'ordenar' => $campo,
                'direcao' => $novaDirecao,
            ]));
        };

        $sortIcon = fn (string $campo) => $ordenarPor === $campo ? ($direcao === 'asc' ? '↑' : '↓') : '↕';
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Jogadores</h2>
                <p class="mt-1 text-sm text-slate-500">Gerencie atletas, posições e valores.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('admin.jogadores.atualizar-vw') }}" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerText = 'Atualizando...';">
                    @csrf
                    <button class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                        Atualizar da Volleyball World
                    </button>
                </form>
                <a href="{{ route('admin.jogadores.create') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-center text-sm font-bold text-white hover:bg-blue-800">Novo jogador</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <form method="GET" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-200 lg:grid-cols-[1fr_220px_220px_auto_auto]">
                <input type="hidden" name="ordenar" value="{{ $ordenarPor }}">
                <input type="hidden" name="direcao" value="{{ $direcao }}">
                <input name="busca" value="{{ request('busca') }}" placeholder="Buscar por nome..." class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">

                <select name="selecao_id" class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas as seleções</option>
                    @foreach ($selecoes as $selecao)
                        <option value="{{ $selecao->id }}" @selected((string) request('selecao_id') === (string) $selecao->id)>{{ $selecao->nome }}</option>
                    @endforeach
                </select>

                <select name="posicao_id" class="rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas as posições</option>
                    @foreach ($posicoes as $posicao)
                        <option value="{{ $posicao->id }}" @selected((string) request('posicao_id') === (string) $posicao->id)>{{ $posicao->nome }} ({{ $posicao->sigla }})</option>
                    @endforeach
                </select>

                <button class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Filtrar</button>
                <a href="{{ route('admin.jogadores.index') }}" class="rounded-lg bg-slate-100 px-5 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-200">Limpar</a>
            </form>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <a href="{{ $sortUrl('nome') }}" class="inline-flex items-center gap-1 hover:text-blue-700">Jogador <span>{{ $sortIcon('nome') }}</span></a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <a href="{{ $sortUrl('selecao') }}" class="inline-flex items-center gap-1 hover:text-blue-700">Seleção <span>{{ $sortIcon('selecao') }}</span></a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <a href="{{ $sortUrl('posicao') }}" class="inline-flex items-center gap-1 hover:text-blue-700">Posição <span>{{ $sortIcon('posicao') }}</span></a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    <a href="{{ $sortUrl('valor_creditos') }}" class="inline-flex items-center gap-1 hover:text-blue-700">Valor <span>{{ $sortIcon('valor_creditos') }}</span></a>
                                </th>
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
