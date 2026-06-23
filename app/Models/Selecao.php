<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Selecao extends Model
{
    use HasFactory;

    protected $table = 'selecoes';

    protected $fillable = [
        'nome',
        'genero',
        'sigla',
        'external_ref',
        'bandeira',
        'source_url',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function jogadores(): HasMany
    {
        return $this->hasMany(Jogador::class);
    }

    public function partidasCasa(): HasMany
    {
        return $this->hasMany(Partida::class, 'selecao_casa_id');
    }

    public function partidasFora(): HasMany
    {
        return $this->hasMany(Partida::class, 'selecao_fora_id');
    }

    public function classificacoes(): HasMany
    {
        return $this->hasMany(Classificacao::class);
    }
}
