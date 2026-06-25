<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelecaoRequest;
use App\Models\Selecao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SelecaoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->only(['selecao', 'genero']);

        $selecoes = Selecao::query()
            ->withCount('jogadores')
            ->when($filtros['selecao'] ?? null, function ($query, string $selecao) {
                $query->where(function ($query) use ($selecao) {
                    $query->where('nome', 'like', "%{$selecao}%")
                        ->orWhere('sigla', 'like', "%{$selecao}%");
                });
            })
            ->when(in_array($filtros['genero'] ?? null, ['masculino', 'feminino'], true), function ($query) use ($filtros) {
                $query->where('genero', $filtros['genero']);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.selecoes.index', compact('selecoes', 'filtros'));
    }

    public function create(): View
    {
        return view('admin.selecoes.create');
    }

    public function store(SelecaoRequest $request): RedirectResponse
    {
        $selecao = Selecao::create($request->validated());
        $this->desativarJogadoresSeNecessario($selecao);

        return redirect()
            ->route('admin.selecoes.show', $selecao)
            ->with('success', 'Selecao cadastrada com sucesso.');
    }

    public function show(Selecao $selecao): View
    {
        $selecao->load(['jogadores.posicao']);

        return view('admin.selecoes.show', compact('selecao'));
    }

    public function edit(Selecao $selecao): View
    {
        return view('admin.selecoes.edit', compact('selecao'));
    }

    public function update(SelecaoRequest $request, Selecao $selecao): RedirectResponse
    {
        DB::transaction(function () use ($request, $selecao): void {
            $selecao->update($request->validated());
            $this->desativarJogadoresSeNecessario($selecao);
        });

        return redirect()
            ->route('admin.selecoes.show', $selecao)
            ->with('success', 'Selecao atualizada com sucesso.');
    }

    public function status(Request $request, Selecao $selecao): RedirectResponse
    {
        $request->validate([
            'ativo' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $selecao): void {
            $selecao->update(['ativo' => $request->boolean('ativo')]);
            $this->desativarJogadoresSeNecessario($selecao);
        });

        return back()->with(
            'success',
            $selecao->ativo
                ? 'Selecao ativada.'
                : 'Selecao desativada. Seus jogadores tambem foram desativados.'
        );
    }

    public function destroy(Selecao $selecao): RedirectResponse
    {
        $selecao->delete();

        return redirect()
            ->route('admin.selecoes.index')
            ->with('success', 'Selecao excluida com sucesso.');
    }

    private function desativarJogadoresSeNecessario(Selecao $selecao): void
    {
        if ($selecao->ativo) {
            return;
        }

        $selecao->jogadores()->update(['ativo' => false]);
    }
}
