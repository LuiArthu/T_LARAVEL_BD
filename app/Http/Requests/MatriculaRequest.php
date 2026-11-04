<?php

namespace App\Http\Requests;

use App\Models\Matricula;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Usada pelo CRUD web e pela API de matrículas. */
class MatriculaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'modalidade' => [
                'required',
                Rule::in(Matricula::MODALIDADES),
                // Um cliente não pode ter duas matrículas na mesma modalidade.
                Rule::unique('matriculas', 'modalidade')
                    ->where(fn ($query) => $query->where('cliente_id', $this->input('cliente_id')))
                    ->ignore($this->route('matricula')),
            ],
            'faixa' => ['required', Rule::in([...Matricula::FAIXAS, Matricula::FAIXA_NAO_SE_APLICA])],
            'data_inicio' => ['required', 'date'],
        ];
    }

    /**
     * Modalidades com graduação exigem uma faixa real. (Nas sem graduação,
     * o MatriculaService grava "N/A" qualquer que seja o valor enviado.)
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (
                    Matricula::usaFaixa($this->input('modalidade'))
                    && $this->input('faixa') === Matricula::FAIXA_NAO_SE_APLICA
                    && ! $validator->errors()->has('faixa')
                ) {
                    $validator->errors()->add('faixa', 'Informe a faixa: esta modalidade tem graduação.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'Cliente não encontrado.',
            'modalidade.unique' => 'Este cliente já possui matrícula nesta modalidade.',
        ];
    }

    public function attributes(): array
    {
        return ['cliente_id' => 'cliente', 'data_inicio' => 'data de início'];
    }
}
