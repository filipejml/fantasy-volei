<x-app-layout>
    <x-slot name="header"><div class="flex justify-between"><h2 class="text-xl font-bold">Partidas</h2><a href="{{ route('admin.partidas.create') }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-bold text-white">Nova partida</a></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('admin.partials.flash')
        <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="min-w-full divide-y">
                <thead class="bg-slate-50"><tr>@foreach (['Data','Jogo','Placar','Status','Origem',''] as $h)<th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
                <tbody class="divide-y">
                @forelse($partidas as $partida)
                    <tr><td class="px-5 py-4 text-sm">{{ $partida->data_partida->format('d/m/Y H:i') }}</td><td class="px-5 py-4 font-bold">{{ $partida->selecaoCasa->nome }} x {{ $partida->selecaoFora->nome }}</td><td class="px-5 py-4 text-lg font-extrabold">{{ $partida->placar_casa ?? '-' }} : {{ $partida->placar_fora ?? '-' }}</td><td class="px-5 py-4 text-sm">{{ str($partida->status)->replace('_',' ')->title() }}</td><td class="px-5 py-4 text-sm">{{ ucfirst($partida->origem) }}</td><td class="px-5 py-4 text-right"><a href="{{ route('admin.partidas.edit',$partida) }}" class="font-bold text-blue-700">Editar</a></td></tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center text-slate-500">Nenhuma partida cadastrada.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $partidas->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
