<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Jogador extends Model
{
    use HasFactory;

    protected $fillable = [
        'selecao_id',
        'posicao_id',
        'nome',
        'genero',
        'valor_creditos',
        'media_pontos',
        'api_player_id',
        'idade',
        'altura',
        'foto',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'valor_creditos' => 'decimal:2',
            'media_pontos' => 'decimal:2',
            'altura' => 'decimal:2',
            'ativo' => 'boolean',
            'api_player_id' => 'integer',
            'idade' => 'integer',
        ];
    }

    public function selecao(): BelongsTo
    {
        return $this->belongsTo(Selecao::class);
    }

    public function posicao(): BelongsTo
    {
        return $this->belongsTo(Posicao::class);
    }
}
