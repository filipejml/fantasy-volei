<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelecaoRequest;
use App\Models\Selecao;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SelecaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $selecoes = Selecao::query()
            ->withCount('jogadores')
            ->latest()
            ->paginate(10);

        return view('admin.selecoes.index', compact('selecoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.selecoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SelecaoRequest $request): RedirectResponse
    {
        $selecao = Selecao::create($request->validated());

        return redirect()
            ->route('admin.selecoes.show', $selecao)
            ->with('success', 'Seleção cadastrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Selecao $selecao): View
    {
        $selecao->load(['jogadores.posicao']);

        return view('admin.selecoes.show', compact('selecao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Selecao $selecao): View
    {
        return view('admin.selecoes.edit', compact('selecao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SelecaoRequest $request, Selecao $selecao): RedirectResponse
    {
        $selecao->update($request->validated());

        return redirect()
            ->route('admin.selecoes.show', $selecao)
            ->with('success', 'Seleção atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Selecao $selecao): RedirectResponse
    {
        $selecao->delete();

        return redirect()
            ->route('admin.selecoes.index')
            ->with('success', 'Seleção excluída com sucesso.');
    }
}
