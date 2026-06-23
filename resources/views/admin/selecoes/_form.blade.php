@php($selecao = $selecao ?? null)

<div class="grid gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="nome" value="Nome da seleção" />
        <x-text-input id="nome" name="nome" class="mt-2 block w-full" :value="old('nome', $selecao?->nome)" required autofocus />
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="genero" value="Gênero" />
        <select id="genero" name="genero" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">Selecione</option>
            <option value="masculino" @selected(old('genero', $selecao?->genero) === 'masculino')>Masculino</option>
            <option value="feminino" @selected(old('genero', $selecao?->genero) === 'feminino')>Feminino</option>
        </select>
        <x-input-error :messages="$errors->get('genero')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sigla" value="Sigla" />
        <x-text-input id="sigla" name="sigla" class="mt-2 block w-full uppercase" :value="old('sigla', $selecao?->sigla)" maxlength="5" />
        <x-input-error :messages="$errors->get('sigla')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="api_team_id" value="ID na API (opcional)" />
        <x-text-input id="api_team_id" name="api_team_id" type="number" min="1" class="mt-2 block w-full" :value="old('api_team_id', $selecao?->api_team_id)" />
        <x-input-error :messages="$errors->get('api_team_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ativo" value="Status" />
        <select id="ativo" name="ativo" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="1" @selected((string) old('ativo', (int) ($selecao?->ativo ?? true)) === '1')>Ativa</option>
            <option value="0" @selected((string) old('ativo', (int) ($selecao?->ativo ?? true)) === '0')>Inativa</option>
        </select>
        <x-input-error :messages="$errors->get('ativo')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="bandeira" value="URL da bandeira (opcional)" />
        <x-text-input id="bandeira" name="bandeira" type="url" class="mt-2 block w-full" :value="old('bandeira', $selecao?->bandeira)" placeholder="https://..." />
        <x-input-error :messages="$errors->get('bandeira')" class="mt-2" />
    </div>
</div>

<div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
    <a href="{{ route('admin.selecoes.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">
        Cancelar
    </a>
    <button type="submit" class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800">
        {{ $submitLabel }}
    </button>
</div>
