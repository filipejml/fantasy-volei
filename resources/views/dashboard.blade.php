<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (Auth::user()->isAdmin())
                <div class="mb-6 rounded-2xl bg-gradient-to-r from-blue-800 to-blue-600 p-8 text-white shadow-lg">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-yellow-300">Painel administrativo</p>
                    <h1 class="mt-2 text-3xl font-extrabold">Olá, {{ Auth::user()->name }}!</h1>
                    <p class="mt-2 text-blue-100">Cadastre as seleções e os jogadores que participarão do fantasy.</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <a href="{{ route('admin.selecoes.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-400 text-2xl">🌎</div>
                        <h2 class="mt-5 text-xl font-extrabold text-slate-900">Gerenciar seleções</h2>
                        <p class="mt-2 text-slate-500">Cadastre, consulte, edite e remova seleções.</p>
                        <span class="mt-5 inline-block font-bold text-blue-700 group-hover:text-blue-900">Acessar →</span>
                    </a>
                    <a href="{{ route('admin.jogadores.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">🏐</div>
                        <h2 class="mt-5 text-xl font-extrabold text-slate-900">Gerenciar jogadores</h2>
                        <p class="mt-2 text-slate-500">Controle atletas, posições, valores e disponibilidade.</p>
                        <span class="mt-5 inline-block font-bold text-blue-700 group-hover:text-blue-900">Acessar →</span>
                    </a>
                    <a href="{{ route('admin.partidas.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-2xl">📅</div>
                        <h2 class="mt-5 text-xl font-extrabold text-slate-900">Partidas e resultados</h2>
                        <p class="mt-2 text-slate-500">Cadastre ou corrija manualmente jogos e placares.</p>
                        <span class="mt-5 inline-block font-bold text-blue-700">Acessar →</span>
                    </a>
                    <a href="{{ route('admin.scraping.index') }}" class="group rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-2xl">↻</div>
                        <h2 class="mt-5 text-xl font-extrabold text-slate-900">Atualizar VNL</h2>
                        <p class="mt-2 text-slate-500">Execute o scraping e consulte logs de importação.</p>
                        <span class="mt-5 inline-block font-bold text-blue-700">Acessar →</span>
                    </a>
                </div>
            @else
                <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="text-gray-900">Você está conectado!</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
