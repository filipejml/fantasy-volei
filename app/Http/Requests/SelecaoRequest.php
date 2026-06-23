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
            'external_ref' => ['nullable', 'string', 'max:100'],
            'bandeira' => ['nullable', 'url', 'max:2048'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'ativo' => ['required', 'boolean'],
        ];
    }
}
