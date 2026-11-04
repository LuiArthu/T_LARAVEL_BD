@php($entrada = 'w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500')

<form action="{{ $action }}" method="POST">
    @csrf
    @if ($matricula->exists)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <x-crud.field name="cliente_id" label="Cliente" :span="true">
            <select id="cliente_id" name="cliente_id" class="{{ $entrada }}">
                <option value="">Selecione o cliente</option>
                @foreach ($clientes as $c)
                    <option value="{{ $c->id }}" @selected((string) old('cliente_id', $matricula->cliente_id) === (string) $c->id)>
                        {{ $c->nome }} — {{ $c->cpf }}
                    </option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="modalidade" label="Modalidade">
            <select id="modalidade" name="modalidade" class="{{ $entrada }}">
                <option value="">Selecione</option>
                @foreach (\App\Models\Matricula::MODALIDADES as $mod)
                    <option value="{{ $mod }}" @selected(old('modalidade', $matricula->modalidade) === $mod)>{{ $mod }}</option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="faixa" label="Faixa">
            <select id="faixa" name="faixa" class="{{ $entrada }}">
                <option value="">Selecione</option>
                <option value="N/A" @selected(old('faixa', $matricula->faixa) === 'N/A')>N/A (modalidade sem graduação)</option>
                @foreach (\App\Models\Matricula::FAIXAS as $f)
                    <option value="{{ $f }}" @selected(old('faixa', $matricula->faixa) === $f)>{{ $f }}</option>
                @endforeach
            </select>
        </x-crud.field>

        <x-crud.field name="data_inicio" label="Data de início">
            <input type="date" id="data_inicio" name="data_inicio"
                   value="{{ old('data_inicio', $matricula->data_inicio?->toDateString() ?? now()->toDateString()) }}"
                   class="{{ $entrada }}">
        </x-crud.field>

    </div>

    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
        <a href="{{ $matricula->exists ? route('matriculas.show', $matricula) : route('matriculas.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</a>

        <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">
            {{ $matricula->exists ? 'Salvar alterações' : 'Cadastrar matrícula' }}
        </button>
    </div>
</form>

<script>
    // Conveniência: Boxe, Muay Thai e Personal não têm faixa. O servidor
    // aplica a mesma regra, então isto só poupa um clique.
    (function () {
        const semFaixa = {{ Js::from(\App\Models\Matricula::SEM_FAIXA) }};
        const modalidade = document.getElementById('modalidade');
        const faixa = document.getElementById('faixa');

        modalidade.addEventListener('change', function () {
            if (semFaixa.includes(modalidade.value)) {
                faixa.value = 'N/A';
            } else if (faixa.value === 'N/A') {
                faixa.value = '';
            }
        });
    })();
</script>
