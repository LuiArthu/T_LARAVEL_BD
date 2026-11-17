{{-- Linhas de historico_faixa (tabela preenchida pelo trigger trg_historico_faixa). --}}
@forelse ($historico as $h)
    <div class="flex items-center justify-between px-6 py-3 border-b border-gray-50 text-sm">
        <span class="text-gray-800">
            @if ($mostrarCliente ?? false)
                <a href="{{ route('clientes.show', $h->cliente_id) }}" class="font-medium text-blue-600 hover:underline">{{ $h->nome ?? 'Cliente #'.$h->cliente_id }}</a> ·
            @endif
            {{ $h->modalidade }}: <strong>{{ $h->faixa_anterior }}</strong> → <strong>{{ $h->faixa_nova }}</strong>
        </span>
        <span class="text-gray-500">{{-- O Neon grava now() em UTC; mostramos no horário de Brasília. --}}
            {{ \Illuminate\Support\Carbon::parse($h->data_alteracao, 'UTC')->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i') }}</span>
    </div>
@empty
    <p class="px-6 py-4 text-sm text-gray-500">Nenhuma troca de faixa registrada.</p>
@endforelse
