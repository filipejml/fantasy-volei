<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    protected $fillable = [
        'genero',
        'temporada',
        'fase',
        'rodada',
        'local',
        'selecao_casa_id',
        'selecao_fora_id',
        'data_partida',
        'placar_casa',
        'placar_fora',
        'sets',
        'status',
        'external_hash',
        'source_url',
        'origem',
        'importado_em',
    ];

    protected function casts(): array
    {
        return [
            'temporada' => 'integer',
            'data_partida' => 'datetime',
            'sets' => 'array',
            'importado_em' => 'datetime',
        ];
    }

    public function selecaoCasa(): BelongsTo
    {
        return $this->belongsTo(Selecao::class, 'selecao_casa_id');
    }

    public function selecaoFora(): BelongsTo
    {
        return $this->belongsTo(Selecao::class, 'selecao_fora_id');
    }
}
