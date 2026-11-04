<?php

namespace App\Http\Requests;

use App\Models\Tamanho;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TamanhoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'tamanho_faixa' => ['required', Rule::in(Tamanho::TAMANHOS)],
            'tamanho_kimono' => ['required', Rule::in(Tamanho::TAMANHOS)],
        ];
    }

    public function messages(): array
    {
        return ['cliente_id.exists' => 'Cliente não encontrado.'];
    }

    public function attributes(): array
    {
        return [
            'cliente_id' => 'cliente',
            'tamanho_faixa' => 'tamanho da faixa',
            'tamanho_kimono' => 'tamanho do kimono',
        ];
    }
}
