<?php

namespace App\Http\Controllers;

use App\Http\Requests\JogadorRequest;
use App\Models\Jogador;
use App\Models\Posicao;
use App\Models\Selecao;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JogadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $jogadores = Jogador::query()
            ->with(['selecao', 'posicao'])
            ->when(request('busca'), function ($query, $busca) {
                $query->where('nome', 'like', "%{$busca}%");
            })
            ->when(request('selecao_id'), function ($query, $selecaoId) {
                $query->where('selecao_id', $selecaoId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selecoes = Selecao::orderBy('nome')->get(['id', 'nome']);

        return view('admin.jogadores.index', compact('jogadores', 'selecoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.jogadores.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JogadorRequest $request): RedirectResponse
    {
        $jogador = Jogador::create($request->validated());

        return redirect()
            ->route('admin.jogadores.show', $jogador)
            ->with('success', 'Jogador cadastrado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jogador $jogador): View
    {
        $jogador->load(['selecao', 'posicao']);

        return view('admin.jogadores.show', compact('jogador'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jogador $jogador): View
    {
        return view('admin.jogadores.edit', [
            'jogador' => $jogador,
            ...$this->formOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JogadorRequest $request, Jogador $jogador): RedirectResponse
    {
        $jogador->update($request->validated());

        return redirect()
            ->route('admin.jogadores.show', $jogador)
            ->with('success', 'Jogador atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jogador $jogador): RedirectResponse
    {
        $jogador->delete();

        return redirect()
            ->route('admin.jogadores.index')
            ->with('success', 'Jogador excluído com sucesso.');
    }

    private function formOptions(): array
    {
        return [
            'selecoes' => Selecao::where('ativo', true)->orderBy('nome')->get(),
            'posicoes' => Posicao::orderBy('nome')->get(),
        ];
    }
}
