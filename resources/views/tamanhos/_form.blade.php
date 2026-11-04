@php($entrada = 'w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500')

<form action="{{ $action }}" method="POST">
    @csrf
    @if ($tamanho->exists)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <x-crud.field name="cliente_id" label="Cliente" :span="true">
            <select id="cliente_id" name="cliente_id" class="{{ $entrada }}">
                <option value="">Selecione o cliente</option>
                @foreach ($clientes as $c)
                    <option value="{{ $c->id }}" @selected((string) old('cliente_id', $tamanho->cliente_id) === (string) $c->id)>
                        {{ $c->nome }} — {{ $c->cpf }}
                    </option>
                @endforeach
            </select>
        </x-crud.field>

        @foreach (['tamanho_faixa' => 'Tamanho da faixa', 'tamanho_kimono' => 'Tamanho do kimono'] as $campo => $rotulo)
            <x-crud.field :name="$campo" :label="$rotulo">
                <select id="{{ $campo }}" name="{{ $campo }}" class="{{ $entrada }}">
                    <option value="">Selecione</option>
                    @foreach (\App\Models\Tamanho::TAMANHOS as $t)
                        <option value="{{ $t }}" @selected(old($campo, $tamanho->{$campo}) === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </x-crud.field>
        @endforeach

    </div>

    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
        <a href="{{ $tamanho->exists ? route('tamanhos.show', $tamanho) : route('tamanhos.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</a>

        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">
            {{ $tamanho->exists ? 'Salvar alterações' : 'Cadastrar tamanhos' }}
        </button>
    </div>
</form>
