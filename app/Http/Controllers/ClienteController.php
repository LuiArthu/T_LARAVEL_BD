<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaRecurso;
use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use App\Services\BancoService;
use App\Services\ClienteService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class ClienteController extends Controller implements HasMiddleware
{
    use AutorizaRecurso;

    public function __construct(private readonly ClienteService $clientes) {}

    public static function middleware(): array
    {
        return self::autorizacoes(Cliente::class, 'cliente');
    }

    public function index(Request $request): View
    {
        $filtros = $request->only('busca');

        return view('clientes.index', [
            'clientes' => $this->clientes->listar($filtros),
            'filtros' => $filtros,
        ]);
    }

    public function create(): View
    {
        return view('clientes.create', ['cliente' => new Cliente]);
    }

    public function store(ClienteRequest $request): RedirectResponse
    {
        $cliente = $this->clientes->criar($request->validated());

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente cadastrado!');
    }

    public function show(Cliente $cliente, BancoService $banco): View
    {
        return view('clientes.show', [
            'cliente' => $this->clientes->detalhar($cliente),
            // Tabela preenchida pelo trigger trg_historico_faixa (só no PostgreSQL).
            'historicoFaixas' => $banco->disponivel() ? $banco->historicoFaixas($cliente->id) : null,
        ]);
    }

    public function edit(Cliente $cliente): View
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(ClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $this->clientes->atualizar($cliente, $request->validated());

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente atualizado!');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        try {
            $this->clientes->excluir($cliente);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente removido!');
    }
}
