@php
    $time = $time ?? null;
    $selecionados = collect(old('jogadores', $time?->jogadores?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
    $generoSelecionado = old('genero', $generoSelecionado ?? $time?->genero ?? 'masculino');
    $limiteInicial = old('creditos_limite', $time?->creditos_limite ?? 100);
@endphp

<div
    x-data="teamBuilder({
        limit: {{ (float) $limiteInicial }},
        selected: @js($selecionados),
        players: @js($jogadores->mapWithKeys(fn ($jogador) => [$jogador->id => (float) $jogador->valor_creditos])),
    })"
    class="grid gap-6 lg:grid-cols-[360px_1fr]"
>
    <aside class="space-y-6">
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="text-lg font-extrabold text-slate-900">Dados do time</h3>
            <div class="mt-5 space-y-4">
                <div>
                    <x-input-label for="nome" value="Nome do time" />
                    <x-text-input id="nome" name="nome" class="mt-2 w-full" :value="old('nome', $time?->nome)" required />
                    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="genero" value="Gênero" />
                    <select id="genero" class="mt-2 w-full rounded-md border-gray-300 bg-slate-50 shadow-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                        @foreach(['masculino' => 'Masculino', 'feminino' => 'Feminino'] as $valor => $label)
                            <option value="{{ $valor }}" @selected($generoSelecionado === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="genero" value="{{ $generoSelecionado }}">
                    <x-input-error :messages="$errors->get('genero')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="creditos_limite" value="Limite de créditos" />
                    <x-text-input id="creditos_limite" name="creditos_limite" type="number" step="0.01" min="1" class="mt-2 w-full" x-model.number="limit" required />
                    <x-input-error :messages="$errors->get('creditos_limite')" class="mt-2" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="text-lg font-extrabold text-slate-900">Resumo</h3>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-xs font-bold uppercase text-blue-700">Atletas</p>
                    <p class="mt-1 text-2xl font-extrabold text-slate-900"><span x-text="count"></span>/7</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-xs font-bold uppercase text-emerald-700">Saldo</p>
                    <p class="mt-1 text-2xl font-extrabold text-slate-900">C$ <span x-text="remainingFormatted"></span></p>
                </div>
            </div>

            <div class="mt-5">
                <div class="mb-2 flex justify-between text-xs font-bold text-slate-500">
                    <span>Créditos usados</span>
                    <span>C$ <span x-text="spentFormatted"></span> / <span x-text="limitFormatted"></span></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full" :class="overBudget ? 'bg-red-600' : 'bg-blue-700'" :style="`width: ${progress}%`"></div>
                </div>
                <p class="mt-2 text-sm font-bold text-red-600" x-show="overBudget">O limite de créditos foi ultrapassado.</p>
            </div>

            @if($errors->any())
                <div class="mt-5 rounded-lg bg-red-50 p-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="mt-6 flex gap-3">
                <a href="{{ route('times.index') }}" class="flex-1 rounded-lg bg-slate-100 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-200">Cancelar</a>
                <button type="submit" class="flex-1 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800" :disabled="overBudget || count > 7" :class="{ 'opacity-60': overBudget || count > 7 }">
                    {{ $submitLabel }}
                </button>
            </div>
        </section>
    </aside>

    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Escolha até 7 jogadores</h3>
                <p class="text-sm text-slate-500">{{ $jogadores->count() }} atletas disponíveis para {{ $generoSelecionado }}.</p>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse($jogadores as $jogador)
                <label class="group flex cursor-pointer flex-col rounded-xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/40" :class="{ 'border-blue-500 bg-blue-50': selected.includes({{ $jogador->id }}) }">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="jogadores[]" value="{{ $jogador->id }}" class="mt-1 rounded border-slate-300 text-blue-700 focus:ring-blue-500" x-model.number="selected" @change="enforceLimit($event, {{ $jogador->id }})">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-slate-900">{{ $jogador->nome }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $jogador->posicao->sigla }} · {{ $jogador->selecao->nome }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                        <span class="font-bold text-blue-700">C$ {{ number_format((float) $jogador->valor_creditos, 2, ',', '.') }}</span>
                        <span class="text-slate-500">Média {{ number_format((float) $jogador->media_pontos, 1, ',', '.') }}</span>
                    </div>
                </label>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                    Nenhum jogador disponível para este gênero.
                </div>
            @endforelse
        </div>
    </section>
</div>

<script>
    function teamBuilder({ limit, selected, players }) {
        return {
            limit,
            selected,
            players,
            get count() {
                return this.selected.length;
            },
            get spent() {
                return this.selected.reduce((total, id) => total + (this.players[id] || 0), 0);
            },
            get remaining() {
                return this.limit - this.spent;
            },
            get overBudget() {
                return this.remaining < 0;
            },
            get progress() {
                return this.limit > 0 ? Math.min(100, (this.spent / this.limit) * 100) : 0;
            },
            get spentFormatted() {
                return this.format(this.spent);
            },
            get remainingFormatted() {
                return this.format(this.remaining);
            },
            get limitFormatted() {
                return this.format(this.limit || 0);
            },
            format(value) {
                return Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            enforceLimit(event, id) {
                if (this.selected.length <= 7) {
                    return;
                }

                this.selected = this.selected.filter((selectedId) => selectedId !== id);
                event.target.checked = false;
            },
        };
    }
</script>
