<?php

namespace App\Console\Commands;

use App\Services\VolleyballWorldScraper;
use Illuminate\Console\Command;

class AtualizarVnl extends Command
{
    protected $signature = 'vnl:atualizar';

    protected $description = 'Importa jogos, resultados, seleções e classificação da Volleyball World';

    public function handle(VolleyballWorldScraper $scraper): int
    {
        $this->info('Atualizando dados da VNL...');
        $log = $scraper->atualizarTudo();

        if ($log->status === 'erro') {
            $this->error($log->mensagem);

            return self::FAILURE;
        }

        $this->info($log->mensagem);
        $this->line("Registros processados: {$log->registros_processados}");

        return self::SUCCESS;
    }
}
