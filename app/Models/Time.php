<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'genero',
        'creditos_limite',
        'creditos_usados',
        'pontuacao_total',
    ];

    protected function casts(): array
    {
        return [
            'creditos_limite' => 'decimal:2',
            'creditos_usados' => 'decimal:2',
            'pontuacao_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jogadores(): BelongsToMany
    {
        return $this->belongsToMany(Jogador::class, 'time_jogadors')
            ->withPivot(['tipo', 'slot'])
            ->withTimestamps();
    }
}
