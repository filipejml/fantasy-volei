@php
    $time = $time ?? null;
    $generoSelecionado = old('genero', $generoSelecionado ?? $time?->genero ?? 'masculino');
    $limiteInicial = old('creditos_limite', $time?->creditos_limite ?? 100);
    $slots = [
        'titulares' => [
            'OH' => ['quantidade' => 2, 'label' => 'Ponteiro', 'cor' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-100'],
            'MB' => ['quantidade' => 2, 'label' => 'Central', 'cor' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'],
            'O' => ['quantidade' => 1, 'label' => 'Oposto', 'cor' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'],
            'S' => ['quantidade' => 1, 'label' => 'Levantador', 'cor' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100'],
        ],
        'reservas' => [
            'L' => ['quantidade' => 1, 'label' => 'Libero', 'cor' => 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100'],
            'S' => ['quantidade' => 1, 'label' => 'Levantador', 'cor' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100'],
            'O' => ['quantidade' => 2, 'label' => 'Oposto', 'cor' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200'],
            'MB' => ['quantidade' => 2, 'label' => 'Central', 'cor' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'],
        ],
    ];
    $jogadoresPorPosicao = $jogadores->groupBy(fn ($jogador) => $jogador->posicao?->sigla);
    $selecionados = ['titulares' => [], 'reservas' => []];

    foreach ($slots as $tipo => $posicoes) {
        foreach ($posicoes as $sigla => $config) {
            $selecionados[$tipo][$sigla] = array_fill(0, $config['quantidade'], '');
        }
    }

    if ($time?->jogadores) {
        foreach ($time->jogadores as $jogador) {
            $tipo = $jogador->pivot->tipo === 'reserva' ? 'reservas' : 'titulares';
            $slot = (string) $jogador->pivot->slot;

            if (! str_contains($slot, '_')) {
                continue;
            }

            [$sigla, $indice] = explode('_', $slot, 2);
            $indice = ((int) $indice) - 1;

            if (isset($selecionados[$tipo][$sigla][$indice])) {
                $selecionados[$tipo][$sigla][$indice] = (string) $jogador->id;
            }
        }
    }

    foreach ($slots as $tipo => $posicoes) {
        foreach ($posicoes as $sigla => $config) {
            $selecionados[$tipo][$sigla] = old("{$tipo}.{$sigla}", $selecionados[$tipo][$sigla]);
        }
    }

    $jogadoresJs = $jogadores->mapWithKeys(fn ($jogador) => [
        $jogador->id => [
            'id' => (string) $jogador->id,
            'nome' => $jogador->nome,
            'selecao' => $jogador->selecao->nome,
            'creditos' => (float) $jogador->valor_creditos,
            'media' => (float) $jogador->media_pontos,
        ],
    ]);
    $jogadoresPorPosicaoJs = $jogadoresPorPosicao->map(fn ($grupo) => $grupo->pluck('id')->map(fn ($id) => (string) $id)->values());
@endphp

<div
    x-data="teamBuilder({
        limit: {{ (float) $limiteInicial }},
        selected: @js($selecionados),
        players: @js($jogadoresJs),
        playersByPosition: @js($jogadoresPorPosicaoJs),
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
                    <x-input-label for="genero" value="Genero" />
                    <select id="genero" class="mt-2 w-full rounded-md border-gray-300 bg-slate-50 shadow-sm focus:border-blue-500 focus:ring-blue-500" disabled>
                        @foreach(['masculino' => 'Masculino', 'feminino' => 'Feminino'] as $valor => $label)
                            <option value="{{ $valor }}" @selected($generoSelecionado === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="genero" value="{{ $generoSelecionado }}">
                    <x-input-error :messages="$errors->get('genero')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="creditos_limite" value="Limite de creditos" />
                    <x-text-input id="creditos_limite" name="creditos_limite" type="number" step="0.01" min="1" class="mt-2 w-full" x-model.number="limit" required />
                    <x-input-error :messages="$errors->get('creditos_limite')" class="mt-2" />
                </div>

                <div class="rounded-xl bg-blue-50 p-4 ring-1 ring-blue-100">
                    <button type="button" class="w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800" @click="randomizeLineup()">
                        Escalacao aleatoria
                    </button>
                    <p class="mt-2 text-xs font-medium text-blue-800">Clique quantas vezes quiser para gerar outra combinacao.</p>
                    <p class="mt-2 text-xs font-bold text-red-600" x-show="randomizeError" x-text="randomizeError"></p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h3 class="text-lg font-extrabold text-slate-900">Resumo</h3>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-blue-50 p-4">
                    <p class="text-xs font-bold uppercase text-blue-700">Atletas</p>
                    <p class="mt-1 text-2xl font-extrabold text-slate-900"><span x-text="count"></span>/12</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-xs font-bold uppercase text-emerald-700">Saldo</p>
                    <p class="mt-1 text-2xl font-extrabold text-slate-900">C$ <span x-text="remainingFormatted"></span></p>
                </div>
            </div>

            <div class="mt-5">
                <div class="mb-2 flex justify-between text-xs font-bold text-slate-500">
                    <span>Creditos usados</span>
                    <span>C$ <span x-text="spentFormatted"></span> / <span x-text="limitFormatted"></span></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full" :class="overBudget ? 'bg-red-600' : 'bg-blue-700'" :style="`width: ${progress}%`"></div>
                </div>
                <p class="mt-2 text-sm font-bold text-red-600" x-show="overBudget">O limite de creditos foi ultrapassado.</p>
                <p class="mt-2 text-sm font-bold text-red-600" x-show="hasDuplicates">Um jogador nao pode ocupar duas vagas.</p>
            </div>

            @if($errors->any())
                <div class="mt-5 rounded-lg bg-red-50 p-3 text-sm font-bold text-red-700">{{ $errors->first() }}</div>
            @endif

            <div class="mt-6 flex gap-3">
                <a href="{{ route('times.index') }}" class="flex-1 rounded-lg bg-slate-100 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-200">Cancelar</a>
                <button type="submit" class="flex-1 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-800" :disabled="overBudget || count !== 12 || hasDuplicates" :class="{ 'opacity-60': overBudget || count !== 12 || hasDuplicates }">
                    {{ $submitLabel }}
                </button>
            </div>
        </section>
    </aside>

    <section class="overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="grid gap-6 xl:grid-cols-[2fr_1fr]">
            <div>
                <div class="flex items-center gap-3 text-slate-900">
                    <span class="relative inline-flex h-5 w-6 items-end">
                        <span class="absolute left-0 top-0 h-2.5 w-2.5 rounded-full bg-blue-700"></span>
                        <span class="absolute left-3 top-1 h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="h-3 w-4 rounded-t-full bg-blue-700"></span>
                    </span>
                    <h3 class="font-extrabold">Titulares</h3>
                    <span class="rounded-full bg-blue-700 px-2.5 py-0.5 text-xs font-extrabold text-white">
                        <span x-text="groupCount('titulares')"></span>/6
                    </span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @foreach($slots['titulares'] as $sigla => $config)
                        @for($indice = 0; $indice < $config['quantidade']; $indice++)
                            @php
                                $campo = "titulares.{$sigla}.{$indice}";
                                $nomeCampo = "titulares[{$sigla}][]";
                                $valorSelecionado = (string) data_get($selecionados, $campo, '');
                            @endphp

                            <label class="relative block min-h-[148px] rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-blue-300 hover:bg-blue-50/40">
                                <select
                                    name="{{ $nomeCampo }}"
                                    class="peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                    x-model="selected.titulares.{{ $sigla }}[{{ $indice }}]"
                                    required
                                >
                                    <option value="">Selecione</option>
                                    @foreach($jogadoresPorPosicao->get($sigla, collect()) as $jogador)
                                        <option value="{{ $jogador->id }}" @selected($valorSelecionado === (string) $jogador->id)>
                                            {{ $jogador->nome }} - {{ $jogador->selecao->nome }} - C$ {{ number_format((float) $jogador->valor_creditos, 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>

                                <span class="absolute right-3 top-3 rounded-full px-2 py-1 text-[11px] font-extrabold {{ $config['cor'] }}">{{ $sigla }}</span>

                                <div class="flex h-full min-h-[116px] flex-col items-center justify-center text-center">
                                    <template x-if="!player(selected.titulares.{{ $sigla }}[{{ $indice }}])">
                                        <div>
                                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full border-2 border-blue-300 bg-white text-3xl leading-none text-blue-700">+</span>
                                            <p class="mt-5 text-sm font-medium text-slate-700">Vaga disponivel</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ $sigla }}</p>
                                        </div>
                                    </template>

                                    <template x-if="player(selected.titulares.{{ $sigla }}[{{ $indice }}])">
                                        <div class="w-full px-1">
                                            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-blue-700 text-sm font-extrabold text-white" x-text="initials(player(selected.titulares.{{ $sigla }}[{{ $indice }}]).nome)"></span>
                                            <p class="mt-4 truncate text-sm font-extrabold text-slate-900" x-text="player(selected.titulares.{{ $sigla }}[{{ $indice }}]).nome"></p>
                                            <p class="mt-1 truncate text-xs text-slate-500" x-text="player(selected.titulares.{{ $sigla }}[{{ $indice }}]).selecao"></p>
                                            <p class="mt-2 text-xs font-bold text-blue-700">C$ <span x-text="format(player(selected.titulares.{{ $sigla }}[{{ $indice }}]).creditos)"></span></p>
                                        </div>
                                    </template>
                                </div>
                            </label>
                            <x-input-error :messages="$errors->get($campo)" class="mt-1" />
                        @endfor
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex items-center gap-3 text-slate-900">
                    <span class="relative inline-flex h-5 w-6">
                        <span class="absolute left-2 top-0 h-2.5 w-2.5 rounded-full border-2 border-blue-700"></span>
                        <span class="absolute bottom-0 left-0 h-3 w-3 rounded-full border-2 border-blue-700"></span>
                        <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-blue-700"></span>
                    </span>
                    <h3 class="font-extrabold">Reservas</h3>
                    <span class="rounded-full bg-blue-700 px-2.5 py-0.5 text-xs font-extrabold text-white">
                        <span x-text="groupCount('reservas')"></span>/6
                    </span>
                </div>

                <div class="mt-6 space-y-2.5">
                    @foreach($slots['reservas'] as $sigla => $config)
                        @for($indice = 0; $indice < $config['quantidade']; $indice++)
                            @php
                                $campo = "reservas.{$sigla}.{$indice}";
                                $nomeCampo = "reservas[{$sigla}][]";
                                $valorSelecionado = (string) data_get($selecionados, $campo, '');
                            @endphp

                            <label class="relative block rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/40">
                                <select
                                    name="{{ $nomeCampo }}"
                                    class="peer absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                                    x-model="selected.reservas.{{ $sigla }}[{{ $indice }}]"
                                    required
                                >
                                    <option value="">Selecione</option>
                                    @foreach($jogadoresPorPosicao->get($sigla, collect()) as $jogador)
                                        <option value="{{ $jogador->id }}" @selected($valorSelecionado === (string) $jogador->id)>
                                            {{ $jogador->nome }} - {{ $jogador->selecao->nome }} - C$ {{ number_format((float) $jogador->valor_creditos, 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="flex min-h-8 items-center gap-3 pr-8">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full border border-slate-300 bg-white">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-700"></span>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <template x-if="!player(selected.reservas.{{ $sigla }}[{{ $indice }}])">
                                            <p class="truncate text-slate-700">Reserva livre</p>
                                        </template>
                                        <template x-if="player(selected.reservas.{{ $sigla }}[{{ $indice }}])">
                                            <p class="truncate font-bold text-slate-900" x-text="player(selected.reservas.{{ $sigla }}[{{ $indice }}]).nome"></p>
                                        </template>
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-extrabold {{ $config['cor'] }}">{{ $sigla }}</span>
                                </div>
                            </label>
                            <x-input-error :messages="$errors->get($campo)" class="mt-1" />
                        @endfor
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function teamBuilder({ limit, selected, players, playersByPosition }) {
        return {
            limit,
            selected,
            players,
            playersByPosition,
            randomizeError: '',
            get selectedIds() {
                return Object.values(this.selected)
                    .flatMap((group) => Object.values(group).flat())
                    .filter((id) => id !== null && id !== '');
            },
            get count() {
                return this.selectedIds.length;
            },
            get hasDuplicates() {
                return new Set(this.selectedIds.map(String)).size !== this.selectedIds.length;
            },
            get spent() {
                return this.selectedIds.reduce((total, id) => total + ((this.players[id] || {}).creditos || 0), 0);
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
            groupCount(group) {
                return Object.values(this.selected[group])
                    .flat()
                    .filter((id) => id !== null && id !== '')
                    .length;
            },
            player(id) {
                return id ? this.players[id] : null;
            },
            initials(name) {
                return String(name || '')
                    .split(' ')
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((part) => part[0])
                    .join('')
                    .toUpperCase();
            },
            format(value) {
                return Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            randomizeLineup() {
                const rules = {
                    titulares: { OH: 2, MB: 2, O: 1, S: 1 },
                    reservas: { L: 1, S: 1, O: 2, MB: 2 },
                };
                const used = new Set();
                const next = { titulares: {}, reservas: {} };

                for (const [group, positions] of Object.entries(rules)) {
                    for (const [position, amount] of Object.entries(positions)) {
                        const available = this.shuffle([...(this.playersByPosition[position] || [])])
                            .filter((id) => !used.has(String(id)));

                        if (available.length < amount) {
                            this.randomizeError = `Nao ha atletas suficientes para ${position}.`;

                            return;
                        }

                        next[group][position] = available.slice(0, amount).map(String);
                        next[group][position].forEach((id) => used.add(id));
                    }
                }

                this.selected = next;
                this.randomizeError = '';
            },
            shuffle(items) {
                for (let index = items.length - 1; index > 0; index--) {
                    const target = Math.floor(Math.random() * (index + 1));
                    [items[index], items[target]] = [items[target], items[index]];
                }

                return items;
            },
        };
    }
</script>
