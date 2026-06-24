<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Editar {{ $time->nome }}</h2>
                <p class="mt-1 text-sm text-slate-500">Ajuste orçamento e atletas do seu elenco.</p>
            </div>
            <form method="POST" action="{{ route('times.destroy', $time) }}" onsubmit="return confirm('Excluir este time?')">
                @csrf
                @method('DELETE')
                <button class="rounded-lg bg-red-50 px-4 py-2.5 text-sm font-bold text-red-700 hover:bg-red-100">Excluir time</button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @include('admin.partials.flash')

            <form method="POST" action="{{ route('times.update', $time) }}">
                @csrf
                @method('PUT')
                @include('times._form', ['submitLabel' => 'Salvar alterações', 'generoSelecionado' => $time->genero])
            </form>
        </div>
    </div>
</x-app-layout>
