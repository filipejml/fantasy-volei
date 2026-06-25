<?php

namespace App\Http\Requests;

use App\Models\Jogador;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'titulares' => ['required', 'array'],
            'titulares.OH' => ['required', 'array', 'size:2'],
            'titulares.OH.*' => ['required', 'integer', 'exists:jogadors,id'],
            'titulares.MB' => ['required', 'array', 'size:2'],
            'titulares.MB.*' => ['required', 'integer', 'exists:jogadors,id'],
            'titulares.O' => ['required', 'array', 'size:1'],
            'titulares.O.*' => ['required', 'integer', 'exists:jogadors,id'],
            'titulares.S' => ['required', 'array', 'size:1'],
            'titulares.S.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas' => ['required', 'array'],
            'reservas.L' => ['required', 'array', 'size:1'],
            'reservas.L.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas.S' => ['required', 'array', 'size:1'],
            'reservas.S.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas.O' => ['required', 'array', 'size:2'],
            'reservas.O.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas.MB' => ['required', 'array', 'size:2'],
            'reservas.MB.*' => ['required', 'integer', 'exists:jogadors,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $ids = collect($this->input('titulares', []))
                ->flatten()
                ->merge(collect($this->input('reservas', []))->flatten())
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($ids->count() !== 12) {
                $validator->errors()->add('jogadores', 'O time deve ter exatamente 12 jogadores.');

                return;
            }

            if ($ids->unique()->count() !== $ids->count()) {
                $validator->errors()->add('jogadores', 'Um jogador nao pode aparecer mais de uma vez no mesmo time.');
            }

            $jogadores = Jogador::with(['posicao', 'selecao'])->whereIn('id', $ids)->get()->keyBy('id');

            if ($jogadores->count() !== $ids->unique()->count()) {
                $validator->errors()->add('jogadores', 'Ha jogadores invalidos na escalacao.');

                return;
            }

            foreach ($jogadores as $jogador) {
                if ($jogador->genero !== $this->input('genero')) {
                    $validator->errors()->add('genero', 'Todos os jogadores devem ser do mesmo genero do time.');

                    break;
                }

                if (! $jogador->ativo || ! $jogador->selecao?->ativo) {
                    $validator->errors()->add('jogadores', 'A escalacao deve conter apenas jogadores ativos de selecoes ativas.');

                    break;
                }
            }

            foreach ($this->slotsEsperados() as $tipo => $posicoes) {
                foreach ($posicoes as $sigla => $quantidade) {
                    $idsDaPosicao = collect($this->input("{$tipo}.{$sigla}", []))->filter()->map(fn ($id) => (int) $id);

                    foreach ($idsDaPosicao as $id) {
                        if (($jogadores[$id]?->posicao?->sigla) !== $sigla) {
                            $validator->errors()->add("{$tipo}.{$sigla}", "A posicao {$sigla} deve receber apenas jogadores dessa posicao.");

                            break 2;
                        }
                    }
                }
            }
        });
    }

    private function slotsEsperados(): array
    {
        return [
            'titulares' => ['OH' => 2, 'MB' => 2, 'O' => 1, 'S' => 1],
            'reservas' => ['L' => 1, 'S' => 1, 'O' => 2, 'MB' => 2],
        ];
    }
}
