<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <table class="min-w-full divide-y">
        <thead class="bg-slate-50">
            <tr>
                @foreach(['#', 'Seleção', 'J', 'V', 'D', 'Sets', 'Pts'] as $h)
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($classificacao as $linha)
                <tr>
                    <td class="px-4 py-4 font-extrabold text-blue-700">{{ $linha->posicao }}</td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            @if($linha->selecao->bandeira)
                                <img src="{{ $linha->selecao->bandeira }}" class="h-7 w-9 object-contain" alt="">
                            @endif
                            <strong>{{ $linha->selecao->nome }}</strong>
                        </div>
                    </td>
                    <td class="px-4 py-4">{{ $linha->jogos }}</td>
                    <td class="px-4 py-4 font-bold text-emerald-700">{{ $linha->vitorias }}</td>
                    <td class="px-4 py-4 font-bold text-red-600">{{ $linha->derrotas }}</td>
                    <td class="px-4 py-4">{{ $linha->sets_pro }}:{{ $linha->sets_contra }}</td>
                    <td class="px-4 py-4 text-lg font-extrabold">{{ $linha->pontos }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-10 text-center text-slate-500">Classificação ainda não cadastrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
