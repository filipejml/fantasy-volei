<?php

namespace App\Http\Controllers;

use App\Http\Requests\TimeRequest;
use App\Models\Jogador;
use App\Models\Time;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        return view('times.index', [
            'times' => $request->user()->times()
                ->with(['jogadores.selecao', 'jogadores.posicao'])
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimeRequest $request): RedirectResponse
    {
        $time = $request->user()->times()->create($request->safe()->except('jogadores'));
        $this->sincronizarJogadores($time, collect($request->input('jogadores', [])));

        return redirect()->route('times.edit', $time)->with('success', 'Time salvo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Time $time): RedirectResponse
    {
        $this->autorizar($request, $time);

        return redirect()->route('times.edit', $time);
    }

    /**
     * Show the form for editing the specified resource.
     */
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

    /**
     * Update the specified resource in storage.
     */
    public function update(TimeRequest $request, Time $time): RedirectResponse
    {
        $this->autorizar($request, $time);
        $time->update($request->safe()->except('jogadores'));
        $this->sincronizarJogadores($time, collect($request->input('jogadores', [])));

        return back()->with('success', 'Time atualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Time $time): RedirectResponse
    {
        $this->autorizar($request, $time);
        $time->delete();

        return redirect()->route('times.index')->with('success', 'Time excluído.');
    }

    public function sugerir(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'genero' => ['required', 'in:masculino,feminino'],
            'creditos_limite' => ['required', 'numeric', 'min:1'],
        ]);

        $disponiveis = Jogador::where('ativo', true)
            ->where('genero', $dados['genero'])
            ->get()
            ->sortByDesc(fn (Jogador $jogador) => (float) $jogador->media_pontos / max((float) $jogador->valor_creditos, 0.01));

        $escolhidos = collect();
        $saldo = (float) $dados['creditos_limite'];

        foreach ($disponiveis as $jogador) {
            if ($escolhidos->count() >= 7) {
                break;
            }

            if ((float) $jogador->valor_creditos <= $saldo) {
                $escolhidos->push($jogador);
                $saldo -= (float) $jogador->valor_creditos;
            }
        }

        $time = $request->user()->times()->create($dados);
        $this->sincronizarJogadores($time, $escolhidos->pluck('id'));

        return redirect()->route('times.edit', $time)
            ->with('success', 'Sugestão criada com base na melhor relação entre média de pontos e créditos.');
    }

    private function sincronizarJogadores(Time $time, Collection $ids): void
    {
        $jogadores = Jogador::whereIn('id', $ids)->where('genero', $time->genero)->get();
        $creditos = $jogadores->sum(fn (Jogador $jogador) => (float) $jogador->valor_creditos);

        abort_if($creditos > (float) $time->creditos_limite, 422, 'O limite de créditos foi ultrapassado.');

        $time->jogadores()->sync($jogadores->pluck('id'));
        $time->update(['creditos_usados' => $creditos]);
    }

    private function autorizar(Request $request, Time $time): void
    {
        abort_unless($time->user_id === $request->user()->id, 403);
    }
}
