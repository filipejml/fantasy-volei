<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Posicao extends Model
{
    protected $table = 'posicaos';

    protected $fillable = [
        'nome',
        'sigla',
    ];

    public function jogadores(): HasMany
    {
        return $this->hasMany(Jogador::class);
    }
}
