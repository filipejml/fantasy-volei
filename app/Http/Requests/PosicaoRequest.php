<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosicaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('posicao')?->id;

        return [
            'nome' => ['required', 'string', 'max:100'],
            'sigla' => ['required', 'string', 'max:3', Rule::unique('posicaos', 'sigla')->ignore($id)],
        ];
    }
}
