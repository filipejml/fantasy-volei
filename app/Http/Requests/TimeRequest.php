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
            'titulares.PON' => ['required', 'array', 'size:2'],
            'titulares.PON.*' => ['required', 'integer', 'exists:jogadors,id'],
            'titulares.CEN' => ['required', 'array', 'size:2'],
            'titulares.CEN.*' => ['required', 'integer', 'exists:jogadors,id'],
            'titulares.OPO' => ['required', 'array', 'size:1'],
            'titulares.OPO.*' => ['required', 'integer', 'exists:jogadors,id'],
            'titulares.LEV' => ['required', 'array', 'size:1'],
            'titulares.LEV.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas' => ['required', 'array'],
            'reservas.LIB' => ['required', 'array', 'size:1'],
            'reservas.LIB.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas.LEV' => ['required', 'array', 'size:1'],
            'reservas.LEV.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas.OPO' => ['required', 'array', 'size:2'],
            'reservas.OPO.*' => ['required', 'integer', 'exists:jogadors,id'],
            'reservas.CEN' => ['required', 'array', 'size:2'],
            'reservas.CEN.*' => ['required', 'integer', 'exists:jogadors,id'],
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
                $validator->errors()->add('jogadores', 'Um jogador não pode aparecer mais de uma vez no mesmo time.');
            }

            $jogadores = Jogador::with('posicao')->whereIn('id', $ids)->get()->keyBy('id');

            if ($jogadores->count() !== $ids->unique()->count()) {
                $validator->errors()->add('jogadores', 'Há jogadores inválidos na escalação.');

                return;
            }

            foreach ($jogadores as $jogador) {
                if ($jogador->genero !== $this->input('genero')) {
                    $validator->errors()->add('genero', 'Todos os jogadores devem ser do mesmo gênero do time.');

                    break;
                }
            }

            foreach ($this->slotsEsperados() as $tipo => $posicoes) {
                foreach ($posicoes as $sigla => $quantidade) {
                    $idsDaPosicao = collect($this->input("{$tipo}.{$sigla}", []))->filter()->map(fn ($id) => (int) $id);

                    foreach ($idsDaPosicao as $id) {
                        if (($jogadores[$id]?->posicao?->sigla) !== $sigla) {
                            $validator->errors()->add("{$tipo}.{$sigla}", "A posição {$sigla} deve receber apenas jogadores dessa posição.");

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
            'titulares' => ['PON' => 2, 'CEN' => 2, 'OPO' => 1, 'LEV' => 1],
            'reservas' => ['LIB' => 1, 'LEV' => 1, 'OPO' => 2, 'CEN' => 2],
        ];
    }
}
