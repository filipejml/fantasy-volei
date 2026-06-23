<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900">Cadastrar seleção</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.selecoes.store') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                @csrf
                @include('admin.selecoes._form', ['submitLabel' => 'Cadastrar seleção'])
            </form>
        </div>
    </div>
</x-app-layout>
