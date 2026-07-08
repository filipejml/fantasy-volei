<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-blue-700">Volleyball Nations League</p>
                <h2 class="mt-1 text-2xl font-extrabold">Classificação VNL {{ $temporada }}</h2>
            </div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                @foreach(['masculino' => 'Masculino', 'feminino' => 'Feminino'] as $valor => $label)
                    <a href="{{ route('vnl.classificacao', ['genero' => $valor, 'temporada' => $temporada]) }}" class="rounded-lg px-4 py-2 text-sm font-bold {{ $genero === $valor ? 'bg-blue-700 text-white' : 'text-slate-600' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section>
                <div class="mb-4">
                    <h3 class="text-xl font-extrabold">Tabela de classificação</h3>
                    <p class="text-sm text-slate-500">Tabela importada ou mantida manualmente pelo administrador.</p>
                </div>

                @include('vnl._classificacao-table', ['classificacao' => $classificacao])
            </section>
        </div>
    </div>
</x-app-layout>
