<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelecaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'genero' => ['required', Rule::in(['masculino', 'feminino'])],
            'sigla' => ['nullable', 'string', 'max:5'],
            'api_team_id' => ['nullable', 'integer', 'min:1'],
            'bandeira' => ['nullable', 'url', 'max:2048'],
            'ativo' => ['required', 'boolean'],
        ];
    }
}
