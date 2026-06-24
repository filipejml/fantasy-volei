<?php

namespace App\Console\Commands;

use App\Services\VolleyballWorldScraper;
use Illuminate\Console\Command;

class AtualizarJogadoresVnl extends Command
{
    protected $signature = 'vnl:atualizar-jogadores';

    protected $description = 'Importa jogadores das seleções da VNL a partir da Volleyball World';

    public function handle(VolleyballWorldScraper $scraper): int
    {
        $this->info('Atualizando jogadores da VNL...');
        $log = $scraper->atualizarJogadores();

        if ($log->status === 'erro') {
            $this->error($log->mensagem);

            return self::FAILURE;
        }

        $this->info($log->mensagem);
        $this->line("Registros processados: {$log->registros_processados}");

        return self::SUCCESS;
    }
}
