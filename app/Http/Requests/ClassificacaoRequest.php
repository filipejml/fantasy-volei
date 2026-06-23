<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassificacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'selecao_id' => ['required', 'exists:selecoes,id'],
            'genero' => ['required', Rule::in(['masculino', 'feminino'])],
            'temporada' => ['required', 'integer', 'min:2020', 'max:2100'],
            'posicao' => ['required', 'integer', 'min:1', 'max:100'],
            'jogos' => ['required', 'integer', 'min:0'],
            'vitorias' => ['required', 'integer', 'min:0'],
            'derrotas' => ['required', 'integer', 'min:0'],
            'pontos' => ['required', 'integer', 'min:0'],
            'sets_pro' => ['required', 'integer', 'min:0'],
            'sets_contra' => ['required', 'integer', 'min:0'],
            'pontos_pro' => ['nullable', 'integer', 'min:0'],
            'pontos_contra' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
