@php($entrada = 'w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500')

<form action="{{ $action }}" method="POST">
    @csrf
    @if ($cliente->exists)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <x-crud.field name="nome" label="Nome completo" :span="true">
            <input type="text" id="nome" name="nome" value="{{ old('nome', $cliente->nome) }}"
                   maxlength="150" placeholder="Digite o nome do cliente" class="{{ $entrada }}">
        </x-crud.field>

        <x-crud.field name="cpf" label="CPF">
            <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $cliente->cpf) }}"
                   maxlength="14" inputmode="numeric" placeholder="000.000.000-00"
                   oninput="formatarCPF(this)" class="{{ $entrada }}">
        </x-crud.field>

        <x-crud.field name="idade" label="Idade">
            <input type="number" id="idade" name="idade" value="{{ old('idade', $cliente->idade) }}"
                   min="1" max="120" placeholder="Ex.: 20" class="{{ $entrada }}">
        </x-crud.field>

        <x-crud.field name="endereco" label="Endereço" :span="true">
            <input type="text" id="endereco" name="endereco" value="{{ old('endereco', $cliente->endereco) }}"
                   maxlength="255" placeholder="Rua, número - bairro - cidade/UF" class="{{ $entrada }}">
        </x-crud.field>

    </div>

    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
        <a href="{{ $cliente->exists ? route('clientes.show', $cliente) : route('clientes.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</a>

        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">
            {{ $cliente->exists ? 'Salvar alterações' : 'Cadastrar cliente' }}
        </button>
    </div>
</form>

<script>
    function formatarCPF(campo) {
        let v = campo.value.replace(/\D/g, '').slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        campo.value = v;
    }
</script>
