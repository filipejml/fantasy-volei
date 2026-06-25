<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Montar time fantasy</h2>
                <p class="mt-1 text-sm text-slate-500">Escolha o genero, defina o orcamento e escale 6 titulares e 6 reservas.</p>
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

            <form method="POST" action="{{ route('times.store') }}">
                @csrf
                @include('times._form', ['submitLabel' => 'Salvar time', 'generoSelecionado' => $genero])
            </form>
        </div>
    </div>
</x-app-layout>
