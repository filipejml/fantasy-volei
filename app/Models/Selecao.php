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
        'api_team_id',
        'bandeira',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'api_team_id' => 'integer',
        ];
    }

    public function jogadores(): HasMany
    {
        return $this->hasMany(Jogador::class);
    }
}
