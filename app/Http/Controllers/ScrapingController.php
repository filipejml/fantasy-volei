<?php

namespace App\Http\Controllers;

use App\Models\ScrapingLog;
use App\Services\VolleyballWorldScraper;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScrapingController extends Controller
{
    public function index(): View
    {
        return view('admin.scraping.index', [
            'logs' => ScrapingLog::latest('iniciado_em')->paginate(20),
        ]);
    }

    public function atualizar(VolleyballWorldScraper $scraper): RedirectResponse
    {
        $log = $scraper->atualizarTudo();

        return back()->with(
            $log->status === 'erro' ? 'error' : 'success',
            $log->mensagem
        );
    }

    public function atualizarJogadores(VolleyballWorldScraper $scraper): RedirectResponse
    {
        $log = $scraper->atualizarJogadores();

        return back()->with(
            $log->status === 'erro' ? 'error' : 'success',
            $log->mensagem
        );
    }
}
