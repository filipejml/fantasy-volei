<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassificacaoRequest;
use App\Models\Classificacao;
use App\Models\Selecao;
use App\Services\ClassificacaoCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassificacaoController extends Controller
{
    public function index(): View
    {
        return view('admin.classificacoes.index', [
            'classificacoes' => Classificacao::with('selecao')
                ->orderByDesc('temporada')
                ->orderBy('genero')
                ->orderBy('posicao')
                ->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.classificacoes.create', [
            'selecoes' => Selecao::orderBy('nome')->get(),
        ]);
    }

    public function store(ClassificacaoRequest $request): RedirectResponse
    {
        Classificacao::updateOrCreate(
            [
                'selecao_id' => $request->integer('selecao_id'),
                'genero' => $request->string('genero'),
                'temporada' => $request->integer('temporada'),
            ],
            [...$this->dados($request), 'origem' => 'manual']
        );

        return redirect()->route('admin.classificacoes.index')->with('success', 'Classificação salva.');
    }

    public function edit(Classificacao $classificacao): View
    {
        return view('admin.classificacoes.edit', [
            'classificacao' => $classificacao,
            'selecoes' => Selecao::orderBy('nome')->get(),
        ]);
    }

    public function update(ClassificacaoRequest $request, Classificacao $classificacao): RedirectResponse
    {
        $classificacao->update([...$this->dados($request), 'origem' => 'manual']);

        return redirect()->route('admin.classificacoes.index')->with('success', 'Classificação atualizada.');
    }

    public function destroy(Classificacao $classificacao): RedirectResponse
    {
        $classificacao->delete();

        return back()->with('success', 'Linha da classificação excluída.');
    }

    public function calcular(Request $request, ClassificacaoCalculator $calculator): RedirectResponse
    {
        $dados = $request->validate([
            'genero' => ['required', 'in:masculino,feminino'],
            'temporada' => ['required', 'integer'],
        ]);

        $total = $calculator->calcular($dados['genero'], $dados['temporada']);

        return back()->with('success', "Classificação recalculada para {$total} seleções.");
    }

    private function dados(ClassificacaoRequest $request): array
    {
        $dados = $request->validated();
        $dados['set_ratio'] = $dados['sets_contra'] > 0 ? $dados['sets_pro'] / $dados['sets_contra'] : null;
        $dados['ponto_ratio'] = ($dados['pontos_contra'] ?? 0) > 0
            ? ($dados['pontos_pro'] ?? 0) / $dados['pontos_contra']
            : null;

        return $dados;
    }
}
