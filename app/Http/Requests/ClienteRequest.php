<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validação de cadastro e edição de cliente (a edição ignora o próprio CPF). */
class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // A autorização por papel é feita nas policies, dentro do controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:150'],
            'idade' => ['required', 'integer', 'between:1,120'],
            'cpf' => [
                'required',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                Rule::unique('clientes', 'cpf')->ignore($this->route('cliente')),
            ],
            'endereco' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.regex' => 'Informe o CPF no formato 000.000.000-00.',
            'cpf.unique' => 'Já existe um cliente com este CPF.',
        ];
    }

    public function attributes(): array
    {
        return ['nome' => 'nome', 'idade' => 'idade', 'cpf' => 'CPF', 'endereco' => 'endereço'];
    }
}
