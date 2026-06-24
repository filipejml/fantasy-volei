<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Montar time fantasy</h2>
                <p class="mt-1 text-sm text-slate-500">Escolha o gênero, defina o orçamento e selecione até 7 atletas.</p>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                @foreach(['masculino' => 'Masculino', 'feminino' => 'Feminino'] as $valor => $label)
                    <a href="{{ route('times.create', ['genero' => $valor]) }}" class="rounded-lg px-4 py-2 text-sm font-bold {{ $genero === $valor ? 'bg-blue-700 text-white' : 'text-slate-600 hover:text-slate-900' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <form method="POST" action="{{ route('times.sugerir') }}" class="mb-6 rounded-2xl bg-yellow-50 p-5 ring-1 ring-yellow-200">
                @csrf
                <input type="hidden" name="genero" value="{{ $genero }}">
                <div class="grid gap-4 lg:grid-cols-[1fr_180px_auto] lg:items-end">
                    <div>
                        <label for="sugestao_nome" class="text-sm font-bold text-slate-700">Nome do time sugerido</label>
                        <input id="sugestao_nome" name="nome" value="{{ old('nome', 'Time ideal') }}" class="mt-2 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="sugestao_creditos" class="text-sm font-bold text-slate-700">Créditos</label>
                        <input id="sugestao_creditos" name="creditos_limite" type="number" step="0.01" min="1" value="100" class="mt-2 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                    </div>
                    <button class="rounded-lg bg-yellow-400 px-5 py-2.5 text-sm font-bold text-blue-950 hover:bg-yellow-300">
                        Sugerir time ideal
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('times.store') }}">
                @csrf
                @include('times._form', ['submitLabel' => 'Salvar time', 'generoSelecionado' => $genero])
            </form>
        </div>
    </div>
</x-app-layout>
