<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'genero' => ['required', Rule::in(['masculino', 'feminino'])],
            'temporada' => ['required', 'integer', 'min:2020', 'max:2100'],
            'fase' => ['nullable', 'string', 'max:100'],
            'rodada' => ['nullable', 'string', 'max:100'],
            'local' => ['nullable', 'string', 'max:255'],
            'selecao_casa_id' => ['required', 'different:selecao_fora_id', 'exists:selecoes,id'],
            'selecao_fora_id' => ['required', 'different:selecao_casa_id', 'exists:selecoes,id'],
            'data_partida' => ['required', 'date'],
            'placar_casa' => ['nullable', 'integer', 'min:0', 'max:5'],
            'placar_fora' => ['nullable', 'integer', 'min:0', 'max:5'],
            'status' => ['required', Rule::in(['agendado', 'ao_vivo', 'encerrado', 'adiado', 'cancelado', 'a_definir'])],
            'source_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
