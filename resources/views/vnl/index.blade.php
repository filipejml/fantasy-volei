<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[.2em] text-blue-700">Volleyball Nations League</p><h2 class="mt-1 text-2xl font-extrabold">VNL {{ $temporada }}</h2></div>
            <div class="inline-flex rounded-xl bg-slate-100 p-1">
                @foreach(['masculino'=>'Masculino','feminino'=>'Feminino'] as $valor=>$label)
                    <a href="{{ route('vnl.index',['genero'=>$valor,'temporada'=>$temporada]) }}" class="rounded-lg px-4 py-2 text-sm font-bold {{ $genero===$valor?'bg-blue-700 text-white':'text-slate-600' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="py-8"><div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
        <section>
            <div class="mb-4"><h3 class="text-xl font-extrabold">Jogos e resultados</h3><p class="text-sm text-slate-500">Dados persistidos no sistema, importados da Volleyball World ou corrigidos manualmente.</p></div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($jogos as $jogo)
                    <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        <div class="mb-5 flex justify-between text-xs font-bold uppercase text-slate-500"><span>{{ $jogo->data_partida->format('d/m · H:i') }}</span><span class="{{ $jogo->status==='ao_vivo'?'text-red-600':'text-blue-700' }}">{{ str($jogo->status)->replace('_',' ')->title() }}</span></div>
                        @foreach([[$jogo->selecaoCasa,$jogo->placar_casa],[$jogo->selecaoFora,$jogo->placar_fora]] as [$selecao,$placar])
                            <div class="mb-4 flex items-center gap-3">@if($selecao->bandeira)<img src="{{ $selecao->bandeira }}" class="h-9 w-11 object-contain" alt="">@else<span class="flex h-9 w-11 items-center justify-center rounded bg-blue-50">🏐</span>@endif<span class="flex-1 font-bold">{{ $selecao->nome }}</span><strong class="text-2xl">{{ $placar ?? '–' }}</strong></div>
                        @endforeach
                        <div class="mt-4 border-t pt-3 text-xs text-slate-500">{{ collect([$jogo->rodada,$jogo->local])->filter()->join(' · ') }}</div>
                    </article>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl bg-white p-10 text-center text-slate-500">Nenhuma partida cadastrada para esta categoria.</div>
                @endforelse
            </div>
        </section>

        <section>
            <div class="mb-4"><h3 class="text-xl font-extrabold">Classificação</h3><p class="text-sm text-slate-500">Tabela importada ou mantida manualmente pelo administrador.</p></div>
            <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <table class="min-w-full divide-y"><thead class="bg-slate-50"><tr>@foreach(['#','Seleção','J','V','D','Sets','Pts'] as $h)<th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">{{ $h }}</th>@endforeach</tr></thead><tbody class="divide-y">
                @forelse($classificacao as $linha)
                    <tr><td class="px-4 py-4 font-extrabold text-blue-700">{{ $linha->posicao }}</td><td class="px-4 py-4"><div class="flex items-center gap-3">@if($linha->selecao->bandeira)<img src="{{ $linha->selecao->bandeira }}" class="h-7 w-9 object-contain" alt="">@endif<strong>{{ $linha->selecao->nome }}</strong></div></td><td class="px-4 py-4">{{ $linha->jogos }}</td><td class="px-4 py-4 font-bold text-emerald-700">{{ $linha->vitorias }}</td><td class="px-4 py-4 font-bold text-red-600">{{ $linha->derrotas }}</td><td class="px-4 py-4">{{ $linha->sets_pro }}:{{ $linha->sets_contra }}</td><td class="px-4 py-4 text-lg font-extrabold">{{ $linha->pontos }}</td></tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-slate-500">Classificação ainda não cadastrada.</td></tr>
                @endforelse
                </tbody></table>
            </div>
        </section>
    </div></div>
</x-app-layout>
