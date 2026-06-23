<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosicaoRequest;
use App\Models\Posicao;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PosicaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.posicoes.index', [
            'posicoes' => Posicao::withCount('jogadores')->orderBy('nome')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.posicoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PosicaoRequest $request): RedirectResponse
    {
        Posicao::create($request->validated());

        return redirect()->route('admin.posicoes.index')->with('success', 'Posição cadastrada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Posicao $posicao): RedirectResponse
    {
        return redirect()->route('admin.posicoes.edit', $posicao);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Posicao $posicao): View
    {
        return view('admin.posicoes.edit', compact('posicao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PosicaoRequest $request, Posicao $posicao): RedirectResponse
    {
        $posicao->update($request->validated());

        return redirect()->route('admin.posicoes.index')->with('success', 'Posição atualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Posicao $posicao): RedirectResponse
    {
        if ($posicao->jogadores()->exists()) {
            return back()->with('error', 'Não é possível excluir uma posição vinculada a jogadores.');
        }

        $posicao->delete();

        return back()->with('success', 'Posição excluída.');
    }
}
