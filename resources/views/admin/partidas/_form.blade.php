@php($partida = $partida ?? null)
<div class="grid gap-5 md:grid-cols-2">
    <div>
        <x-input-label for="genero" value="Gênero" />
        <select name="genero" id="genero" class="mt-2 w-full rounded-md border-gray-300" required>
            @foreach (['masculino' => 'Masculino', 'feminino' => 'Feminino'] as $valor => $label)
                <option value="{{ $valor }}" @selected(old('genero', $partida?->genero) === $valor)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="temporada" value="Temporada" />
        <x-text-input id="temporada" name="temporada" type="number" class="mt-2 w-full" :value="old('temporada', $partida?->temporada ?? config('services.volleyball_world.season'))" required />
    </div>
    <div>
        <x-input-label for="selecao_casa_id" value="Seleção da casa" />
        <select name="selecao_casa_id" id="selecao_casa_id" class="mt-2 w-full rounded-md border-gray-300" required>
            <option value="">Selecione</option>
            @foreach ($selecoes as $selecao)
                <option value="{{ $selecao->id }}" @selected((string) old('selecao_casa_id', $partida?->selecao_casa_id) === (string) $selecao->id)>{{ $selecao->nome }} ({{ $selecao->genero }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="selecao_fora_id" value="Seleção visitante" />
        <select name="selecao_fora_id" id="selecao_fora_id" class="mt-2 w-full rounded-md border-gray-300" required>
            <option value="">Selecione</option>
            @foreach ($selecoes as $selecao)
                <option value="{{ $selecao->id }}" @selected((string) old('selecao_fora_id', $partida?->selecao_fora_id) === (string) $selecao->id)>{{ $selecao->nome }} ({{ $selecao->genero }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="data_partida" value="Data e horário" />
        <x-text-input id="data_partida" name="data_partida" type="datetime-local" class="mt-2 w-full" :value="old('data_partida', $partida?->data_partida?->format('Y-m-d\\TH:i'))" required />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select name="status" id="status" class="mt-2 w-full rounded-md border-gray-300" required>
            @foreach (['agendado','ao_vivo','encerrado','adiado','cancelado','a_definir'] as $status)
                <option value="{{ $status }}" @selected(old('status', $partida?->status ?? 'agendado') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="placar_casa" value="Sets da casa" />
        <x-text-input id="placar_casa" name="placar_casa" type="number" min="0" max="5" class="mt-2 w-full" :value="old('placar_casa', $partida?->placar_casa)" />
    </div>
    <div>
        <x-input-label for="placar_fora" value="Sets do visitante" />
        <x-text-input id="placar_fora" name="placar_fora" type="number" min="0" max="5" class="mt-2 w-full" :value="old('placar_fora', $partida?->placar_fora)" />
    </div>
    @foreach (['fase' => 'Fase', 'rodada' => 'Rodada', 'local' => 'Local'] as $campo => $label)
        <div>
            <x-input-label :for="$campo" :value="$label" />
            <x-text-input :id="$campo" :name="$campo" class="mt-2 w-full" :value="old($campo, $partida?->{$campo})" />
        </div>
    @endforeach
    <div class="md:col-span-2">
        <x-input-label for="source_url" value="URL de origem (opcional)" />
        <x-text-input id="source_url" name="source_url" type="url" class="mt-2 w-full" :value="old('source_url', $partida?->source_url)" />
    </div>
</div>
@if ($errors->any())
    <div class="mt-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
@endif
<div class="mt-7 flex justify-end gap-3 border-t pt-5">
    <a href="{{ route('admin.partidas.index') }}" class="rounded-lg px-4 py-2 text-sm font-bold text-slate-600">Cancelar</a>
    <button class="rounded-lg bg-blue-700 px-5 py-2 text-sm font-bold text-white">{{ $submitLabel }}</button>
</div>
