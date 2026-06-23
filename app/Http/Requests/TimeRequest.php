<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'genero' => ['required', Rule::in(['masculino', 'feminino'])],
            'creditos_limite' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'jogadores' => ['nullable', 'array', 'max:7'],
            'jogadores.*' => ['integer', 'exists:jogadors,id'],
        ];
    }
}
