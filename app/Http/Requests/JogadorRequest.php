<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JogadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'selecao_id' => ['required', 'integer', 'exists:selecoes,id'],
            'posicao_id' => ['required', 'integer', 'exists:posicaos,id'],
            'nome' => ['required', 'string', 'max:255'],
            'genero' => ['required', Rule::in(['masculino', 'feminino'])],
            'valor_creditos' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'media_pontos' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'api_player_id' => ['nullable', 'integer', 'min:1'],
            'idade' => ['nullable', 'integer', 'min:14', 'max:60'],
            'altura' => ['nullable', 'numeric', 'min:1', 'max:3'],
            'foto' => ['nullable', 'url', 'max:2048'],
            'ativo' => ['required', 'boolean'],
        ];
    }
}
