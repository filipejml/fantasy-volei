<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-bold text-slate-900">Editar jogador</h2></x-slot>
    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.jogadores.update', $jogador) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                @csrf
                @method('PUT')
                @include('admin.jogadores._form', ['submitLabel' => 'Salvar alterações'])
            </form>
        </div>
    </div>
</x-app-layout>
