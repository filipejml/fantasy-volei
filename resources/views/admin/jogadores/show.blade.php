<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-slate-900">Detalhes do jogador</h2>
            <a href="{{ route('admin.jogadores.edit', $jogador) }}" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600">Editar</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                    @if ($jogador->foto)
                        <img src="{{ $jogador->foto }}" alt="{{ $jogador->nome }}" class="h-36 w-36 rounded-2xl object-cover ring-1 ring-slate-200">
                    @else
                        <div class="flex h-36 w-36 items-center justify-center rounded-2xl bg-blue-100 text-5xl font-extrabold text-blue-700">{{ mb_substr($jogador->nome, 0, 1) }}</div>
                    @endif
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-extrabold text-slate-950">{{ $jogador->nome }}</h1>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $jogador->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $jogador->ativo ? 'Ativo' : 'Inativo' }}</span>
                        </div>
                        <p class="mt-2 text-slate-500">{{ $jogador->selecao->nome }} · {{ $jogador->posicao->nome }}</p>
                        <p class="mt-3 text-2xl font-extrabold text-blue-700">C$ {{ number_format($jogador->valor_creditos, 2, ',', '.') }}</p>
                    </div>
                </div>

                <dl class="mt-8 grid gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Média</dt><dd class="mt-1 text-lg font-bold text-slate-900">{{ number_format($jogador->media_pontos, 2, ',', '.') }} pts</dd></div>
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Idade</dt><dd class="mt-1 text-lg font-bold text-slate-900">{{ $jogador->idade ? $jogador->idade.' anos' : '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Altura</dt><dd class="mt-1 text-lg font-bold text-slate-900">{{ $jogador->altura ? number_format($jogador->altura, 2, ',', '.').' m' : '—' }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">Gênero</dt><dd class="mt-1 text-lg font-bold capitalize text-slate-900">{{ $jogador->genero }}</dd></div>
                </dl>

                <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-6">
                    <a href="{{ route('admin.jogadores.index') }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">← Voltar</a>
                    <form method="POST" action="{{ route('admin.jogadores.destroy', $jogador) }}" onsubmit="return confirm('Excluir este jogador?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-bold text-red-600 hover:text-red-800">Excluir jogador</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
