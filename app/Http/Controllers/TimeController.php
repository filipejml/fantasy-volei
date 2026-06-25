<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeRequest;
use App\Models\Jogador;
use App\Models\Time;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeController extends Controller
{
    public function index(Request $request): View
    {
        return view('times.index', [
            'times' => $request->user()->times()
                ->with(['jogadores.selecao', 'jogadores.posicao'])
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $genero = in_array($request->query('genero'), ['masculino', 'feminino'], true)
            ? $request->query('genero')
            : 'masculino';

        return view('times.create', [
            'genero' => $genero,
            'jogadores' => Jogador::with(['selecao', 'posicao'])
                ->where('ativo', true)
                ->where('genero', $genero)
                ->orderBy('posicao_id')
                ->orderByDesc('media_pontos')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function store(TimeRequest $request): RedirectResponse
    {
        $time = $request->user()->times()->create($request->safe()->except(['titulares', 'reservas']));
        $this->sincronizarEscalacao($time, $request->validated());

        return redirect()->route('times.edit', $time)->with('success', 'Time salvo.');
    }

    public function show(Request $request, Time $time): RedirectResponse
    {
        $this->autorizar($request, $time);

        return redirect()->route('times.edit', $time);
    }

    public function edit(Request $request, Time $time): View
    {
        $this->autorizar($request, $time);

        return view('times.edit', [
            'time' => $time->load('jogadores'),
            'jogadores' => Jogador::with(['selecao', 'posicao'])
                ->where('ativo', true)
                ->where('genero', $time->genero)
                ->orderBy('posicao_id')
                ->orderByDesc('media_pontos')
                ->orderBy('nome')
                ->get(),
        ]);
    }

    public function update(TimeRequest $request, Time $time): RedirectResponse
    {
        $this->autorizar($request, $time);
        $time->update($request->safe()->except(['titulares', 'reservas']));
        $this->sincronizarEscalacao($time, $request->validated());

        return back()->with('success', 'Time atualizado.');
    }

    public function destroy(Request $request, Time $time): RedirectResponse
    {
        $this->autorizar($request, $time);
        $time->delete();

        return redirect()->route('times.index')->with('success', 'Time excluido.');
    }

    private function sincronizarEscalacao(Time $time, array $dados): void
    {
        $payload = [];
        $ids = collect();

        foreach ($this->slotsEsperados() as $tipo => $posicoes) {
            foreach ($posicoes as $sigla => $quantidade) {
                foreach (collect($dados[$tipo][$sigla] ?? [])->values() as $indice => $id) {
                    $payload[(int) $id] = [
                        'tipo' => $tipo === 'titulares' ? 'titular' : 'reserva',
                        'slot' => "{$sigla}_".($indice + 1),
                    ];
                    $ids->push((int) $id);
                }
            }
        }

        $jogadores = Jogador::whereIn('id', $ids)->where('genero', $time->genero)->get();
        $creditos = $jogadores->sum(fn (Jogador $jogador) => (float) $jogador->valor_creditos);

        abort_if($creditos > (float) $time->creditos_limite, 422, 'O limite de creditos foi ultrapassado.');

        $time->jogadores()->sync($payload);
        $time->update(['creditos_usados' => $creditos]);
    }

    private function slotsEsperados(): array
    {
        return [
            'titulares' => ['OH' => 2, 'MB' => 2, 'O' => 1, 'S' => 1],
            'reservas' => ['L' => 1, 'S' => 1, 'O' => 2, 'MB' => 2],
        ];
    }

    private function autorizar(Request $request, Time $time): void
    {
        abort_unless($time->user_id === $request->user()->id, 403);
    }
}
