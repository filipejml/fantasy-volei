<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Classificacao extends Model
{
    protected $table = 'classificacoes';

    protected $fillable = [
        'selecao_id',
        'genero',
        'temporada',
        'posicao',
        'jogos',
        'vitorias',
        'derrotas',
        'pontos',
        'sets_pro',
        'sets_contra',
        'set_ratio',
        'pontos_pro',
        'pontos_contra',
        'ponto_ratio',
        'origem',
        'source_url',
        'importado_em',
    ];

    protected function casts(): array
    {
        return [
            'temporada' => 'integer',
            'posicao' => 'integer',
            'set_ratio' => 'decimal:3',
            'ponto_ratio' => 'decimal:3',
            'importado_em' => 'datetime',
        ];
    }

    public function selecao(): BelongsTo
    {
        return $this->belongsTo(Selecao::class);
    }
}
