<?php

namespace App\Http\Requests;

use App\Models\Pagamento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PagamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Checkbox desmarcado não é enviado: sem isso "ativo" falharia na validação. */
    protected function prepareForValidation(): void
    {
        $this->merge(['ativo' => $this->boolean('ativo')]);
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            // O CHECK da tabela só aceita 1–28 (evita dias inexistentes em fevereiro).
            'dia_pagamento' => ['required', 'integer', 'between:1,28'],
            'plano' => ['required', Rule::in(Pagamento::PLANOS)],
            'forma_pagamento' => ['required', Rule::in(Pagamento::FORMAS)],
            'ativo' => ['required', 'boolean'],
            'status_pagamento' => ['required', Rule::in(Pagamento::STATUS)],
        ];
    }

    /**
     * Mesma regra da trigger trg_bloqueio_pagamento (database/sql): pagamento
     * inativo não pode ficar como "Pago". Validar aqui devolve uma mensagem
     * clara (422) em vez de deixar o PostgreSQL recusar com erro 500.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->boolean('ativo') && $this->input('status_pagamento') === 'Pago') {
                $validator->errors()->add(
                    'status_pagamento',
                    'Um pagamento inativo não pode ficar como Pago.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'Cliente não encontrado.',
            'dia_pagamento.between' => 'O dia de pagamento deve ficar entre 1 e 28.',
        ];
    }

    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'dia_pagamento' => 'dia de pagamento',
            'forma_pagamento' => 'forma de pagamento',
            'status_pagamento' => 'situação',
        ];
    }
}
