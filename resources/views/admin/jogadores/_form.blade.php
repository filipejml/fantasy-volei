@php($jogador = $jogador ?? null)

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="nome" value="Nome do jogador" />
        <x-text-input id="nome" name="nome" class="mt-2 block w-full" :value="old('nome', $jogador?->nome)" required autofocus />
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="selecao_id" value="Seleção" />
        <select id="selecao_id" name="selecao_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">Selecione</option>
            @foreach ($selecoes as $selecao)
                <option value="{{ $selecao->id }}" @selected((string) old('selecao_id', request('selecao_id', $jogador?->selecao_id)) === (string) $selecao->id)>
                    {{ $selecao->nome }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('selecao_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="posicao_id" value="Posição" />
        <select id="posicao_id" name="posicao_id" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">Selecione</option>
            @foreach ($posicoes as $posicao)
                <option value="{{ $posicao->id }}" @selected((string) old('posicao_id', $jogador?->posicao_id) === (string) $posicao->id)>
                    {{ $posicao->nome }} ({{ $posicao->sigla }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('posicao_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="genero" value="Gênero" />
        <select id="genero" name="genero" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">Selecione</option>
            <option value="masculino" @selected(old('genero', $jogador?->genero) === 'masculino')>Masculino</option>
            <option value="feminino" @selected(old('genero', $jogador?->genero) === 'feminino')>Feminino</option>
        </select>
        <x-input-error :messages="$errors->get('genero')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ativo" value="Status" />
        <select id="ativo" name="ativo" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="1" @selected((string) old('ativo', (int) ($jogador?->ativo ?? true)) === '1')>Ativo</option>
            <option value="0" @selected((string) old('ativo', (int) ($jogador?->ativo ?? true)) === '0')>Inativo</option>
        </select>
        <x-input-error :messages="$errors->get('ativo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="valor_creditos" value="Valor em créditos" />
        <x-text-input id="valor_creditos" name="valor_creditos" type="number" min="0" step="0.01" class="mt-2 block w-full" :value="old('valor_creditos', $jogador?->valor_creditos)" required />
        <x-input-error :messages="$errors->get('valor_creditos')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="media_pontos" value="Média de pontos" />
        <x-text-input id="media_pontos" name="media_pontos" type="number" min="0" step="0.01" class="mt-2 block w-full" :value="old('media_pontos', $jogador?->media_pontos ?? 0)" />
        <x-input-error :messages="$errors->get('media_pontos')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="idade" value="Idade" />
        <x-text-input id="idade" name="idade" type="number" min="14" max="60" class="mt-2 block w-full" :value="old('idade', $jogador?->idade)" />
        <x-input-error :messages="$errors->get('idade')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="altura" value="Altura em metros" />
        <x-text-input id="altura" name="altura" type="number" min="1" max="3" step="0.01" class="mt-2 block w-full" :value="old('altura', $jogador?->altura)" placeholder="1.90" />
        <x-input-error :messages="$errors->get('altura')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="foto" value="URL da foto (opcional)" />
        <x-text-input id="foto" name="foto" type="url" class="mt-2 block w-full" :value="old('foto', $jogador?->foto)" placeholder="https://..." />
        <x-input-error :messages="$errors->get('foto')" class="mt-2" />
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
    <a href="{{ route('admin.jogadores.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Cancelar</a>
    <button type="submit" class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800">{{ $submitLabel }}</button>
</div>
