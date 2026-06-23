<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScrapingLog extends Model
{
    protected $fillable = [
        'tipo',
        'status',
        'source_url',
        'mensagem',
        'detalhes',
        'registros_processados',
        'iniciado_em',
        'finalizado_em',
    ];

    protected function casts(): array
    {
        return [
            'detalhes' => 'array',
            'iniciado_em' => 'datetime',
            'finalizado_em' => 'datetime',
        ];
    }
}
