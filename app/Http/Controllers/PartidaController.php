<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartidaRequest;
use App\Models\Partida;
use App\Models\Selecao;
use App\Services\VolleyballWorldScraper;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $partidas = Partida::with(['selecaoCasa', 'selecaoFora'])
            ->latest('data_partida')
            ->paginate(15);

        return view('admin.partidas.index', compact('partidas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.partidas.create', ['selecoes' => Selecao::orderBy('nome')->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartidaRequest $request): RedirectResponse
    {
        $partida = Partida::create([
            ...$request->validated(),
            'origem' => 'manual',
        ]);

        return redirect()->route('admin.partidas.edit', $partida)->with('success', 'Partida cadastrada.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Partida $partida): RedirectResponse
    {
        return redirect()->route('admin.partidas.edit', $partida);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partida $partida): View
    {
        return view('admin.partidas.edit', [
            'partida' => $partida,
            'selecoes' => Selecao::orderBy('nome')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PartidaRequest $request, Partida $partida): RedirectResponse
    {
        $partida->update([
            ...$request->validated(),
            'origem' => 'manual',
        ]);

        return back()->with('success', 'Partida atualizada manualmente.');
    }

    public function atualizarPlacar(Partida $partida, VolleyballWorldScraper $scraper): RedirectResponse
    {
        try {
            $scraper->atualizarPartida($partida);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Não foi possível atualizar o placar: '.$exception->getMessage());
        }

        return back()->with('success', 'Placar atualizado pela Volleyball World.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida): RedirectResponse
    {
        $partida->delete();

        return redirect()->route('admin.partidas.index')->with('success', 'Partida excluída.');
    }
}
