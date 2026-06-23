@php($classificacao=$classificacao??null)
<div class="grid gap-5 md:grid-cols-3">
    <div class="md:col-span-2"><x-input-label for="selecao_id" value="Seleção"/><select id="selecao_id" name="selecao_id" class="mt-2 w-full rounded-md border-gray-300" required>@foreach($selecoes as $selecao)<option value="{{ $selecao->id }}" @selected((string)old('selecao_id',$classificacao?->selecao_id)===(string)$selecao->id)>{{ $selecao->nome }} ({{ $selecao->genero }})</option>@endforeach</select></div>
    <div><x-input-label for="genero" value="Gênero"/><select id="genero" name="genero" class="mt-2 w-full rounded-md border-gray-300">@foreach(['masculino','feminino'] as $g)<option value="{{ $g }}" @selected(old('genero',$classificacao?->genero)===$g)>{{ ucfirst($g) }}</option>@endforeach</select></div>
    @foreach(['temporada'=>'Temporada','posicao'=>'Posição','jogos'=>'Jogos','vitorias'=>'Vitórias','derrotas'=>'Derrotas','pontos'=>'Pontos','sets_pro'=>'Sets pró','sets_contra'=>'Sets contra','pontos_pro'=>'Pontos pró','pontos_contra'=>'Pontos contra'] as $campo=>$label)
        <div><x-input-label :for="$campo" :value="$label"/><x-text-input :id="$campo" :name="$campo" type="number" min="0" class="mt-2 w-full" :value="old($campo,$classificacao?->{$campo} ?? ($campo==='temporada'?config('services.volleyball_world.season'):0))" required/></div>
    @endforeach
</div>
@if($errors->any())<div class="mt-5 rounded bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<div class="mt-6 flex justify-end"><button class="rounded-lg bg-blue-700 px-5 py-2 font-bold text-white">{{ $submitLabel }}</button></div>
