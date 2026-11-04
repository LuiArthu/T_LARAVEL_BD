<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaRecurso;
use App\Http\Requests\PagamentoRequest;
use App\Models\Pagamento;
use App\Services\ClienteService;
use App\Services\PagamentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class PagamentoController extends Controller implements HasMiddleware
{
    use AutorizaRecurso;

    public function __construct(
        private readonly PagamentoService $pagamentos,
        private readonly ClienteService $clientes,
    ) {}

    public static function middleware(): array
    {
        return self::autorizacoes(Pagamento::class, 'pagamento');
    }

    public function index(Request $request): View
    {
        $filtros = $request->only(['busca', 'status', 'plano', 'ativo']);

        return view('pagamentos.index', [
            'pagamentos' => $this->pagamentos->listar($filtros),
            'resumo' => $this->pagamentos->resumo(),
            'filtros' => $filtros,
        ]);
    }

    public function create(Request $request): View
    {
        return view('pagamentos.create', [
            'pagamento' => new Pagamento([
                'cliente_id' => $request->integer('cliente_id') ?: null,
                'ativo' => true,
                'status_pagamento' => 'Pago',
            ]),
            'clientes' => $this->clientes->opcoes(),
        ]);
    }

    public function store(PagamentoRequest $request): RedirectResponse
    {
        $pagamento = $this->pagamentos->criar($request->validated());

        return redirect()->route('pagamentos.show', $pagamento)->with('success', 'Pagamento cadastrado!');
    }

    public function show(Pagamento $pagamento): View
    {
        return view('pagamentos.show', ['pagamento' => $this->pagamentos->detalhar($pagamento)]);
    }

    public function edit(Pagamento $pagamento): View
    {
        return view('pagamentos.edit', [
            'pagamento' => $pagamento,
            'clientes' => $this->clientes->opcoes(),
        ]);
    }

    public function update(PagamentoRequest $request, Pagamento $pagamento): RedirectResponse
    {
        $this->pagamentos->atualizar($pagamento, $request->validated());

        return redirect()->route('pagamentos.show', $pagamento)->with('success', 'Pagamento atualizado!');
    }

    public function destroy(Pagamento $pagamento): RedirectResponse
    {
        $this->pagamentos->excluir($pagamento);

        return redirect()->route('pagamentos.index')->with('success', 'Pagamento removido!');
    }
}
