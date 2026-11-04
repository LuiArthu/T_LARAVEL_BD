@php($entrada = 'w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500')

<form action="{{ $action }}" method="POST">
    @csrf
    @if ($pagamento->exists)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <x-crud.field name="cliente_id" label="Cliente" :span="true">
            <select id="cliente_id" name="cliente_id" class="{{ $entrada }}">
                <option value="">Selecione o cliente</option>
                @foreach ($clientes as $c)
                    <option value="{{ $c->id }}" @selected((string) old('cliente_id', $pagamento->cliente_id) === (string) $c->id)>
                        {{ $c->nome }} — {{ $c->cpf }}
                    </option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="plano" label="Plano">
            <select id="plano" name="plano" class="{{ $entrada }}">
                <option value="">Selecione</option>
                @foreach (\App\Models\Pagamento::PLANOS as $v)
                    <option value="{{ $v }}" @selected(old('plano', $pagamento->plano) === $v)>{{ $v }}</option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="forma_pagamento" label="Forma de pagamento">
            <select id="forma_pagamento" name="forma_pagamento" class="{{ $entrada }}">
                <option value="">Selecione</option>
                @foreach (\App\Models\Pagamento::FORMAS as $v)
                    <option value="{{ $v }}" @selected(old('forma_pagamento', $pagamento->forma_pagamento) === $v)>{{ $v === 'Credito' ? 'Crédito' : 'Débito' }}</option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="dia_pagamento" label="Dia do pagamento (1 a 28)">
            <input type="number" id="dia_pagamento" name="dia_pagamento" min="1" max="28"
                   value="{{ old('dia_pagamento', $pagamento->dia_pagamento) }}" class="{{ $entrada }}">
        </x-crud.field>

        <x-crud.field name="status_pagamento" label="Situação">
            <select id="status_pagamento" name="status_pagamento" class="{{ $entrada }}">
                @foreach (\App\Models\Pagamento::STATUS as $v)
                    <option value="{{ $v }}" @selected(old('status_pagamento', $pagamento->status_pagamento) === $v)>{{ $v }}</option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="ativo" label="Cadastro" :span="true">
            {{-- O hidden garante o envio de "0" quando o checkbox está desmarcado. --}}
            <input type="hidden" name="ativo" value="0">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" id="ativo" name="ativo" value="1" @checked(old('ativo', $pagamento->ativo))
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Plano ativo</span>
            </label>
        </x-crud.field>

    </div>

    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
        <a href="{{ $pagamento->exists ? route('pagamentos.show', $pagamento) : route('pagamentos.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</a>

        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">
            {{ $pagamento->exists ? 'Salvar alterações' : 'Cadastrar pagamento' }}
        </button>
    </div>
</form>
