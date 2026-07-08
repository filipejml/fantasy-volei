@php
    $sets = collect($jogo->sets ?? []);
    $ultimoSet = $sets->last();
    $pontosCasa = data_get($ultimoSet, 'pointsTeamA');
    $pontosFora = data_get($ultimoSet, 'pointsTeamB');
    $temPontosAoVivo = $jogo->status === 'ao_vivo' && ($pontosCasa || $pontosFora);
@endphp

<article class="rounded-2xl bg-white p-5 shadow-sm ring-1 {{ $jogo->status === 'ao_vivo' ? 'ring-red-200' : 'ring-slate-200' }}">
    <div class="mb-5 flex justify-between text-xs font-bold uppercase text-slate-500">
        <span>{{ $jogo->data_partida->format('d/m - H:i') }}</span>
        <span class="{{ $jogo->status === 'ao_vivo' ? 'rounded-full bg-red-600 px-3 py-1 text-white' : 'text-blue-700' }}">{{ str($jogo->status)->replace('_', ' ')->title() }}</span>
    </div>

    @foreach([[$jogo->selecaoCasa, $jogo->placar_casa], [$jogo->selecaoFora, $jogo->placar_fora]] as [$selecao, $placar])
        <div class="mb-4 flex items-center gap-3">
            @if($selecao->bandeira)
                <img src="{{ $selecao->bandeira }}" class="h-9 w-11 object-contain" alt="">
            @else
                <span class="flex h-9 w-11 items-center justify-center rounded bg-blue-50 text-sm font-bold text-blue-700">{{ $selecao->sigla ?? 'VNL' }}</span>
            @endif
            <span class="flex-1 font-bold">{{ $selecao->nome }}</span>
            <strong class="text-2xl">{{ $placar !== null && $placar >= 0 ? $placar : 0 }}</strong>
        </div>
    @endforeach

    @if($temPontosAoVivo)
        <div class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm font-bold text-red-700 ring-1 ring-red-100">
            Set atual: {{ $pontosCasa }} x {{ $pontosFora }}
        </div>
    @elseif($sets->isNotEmpty())
        <div class="mt-4 text-xs text-slate-500">
            Parciais: {{ $sets->map(fn ($set) => data_get($set, 'pointsTeamA', 0).'x'.data_get($set, 'pointsTeamB', 0))->join(', ') }}
        </div>
    @endif

    <div class="mt-4 border-t pt-3 text-xs text-slate-500">
        {{ collect([$jogo->rodada, $jogo->local])->filter()->join(' - ') }}
    </div>
</article>
