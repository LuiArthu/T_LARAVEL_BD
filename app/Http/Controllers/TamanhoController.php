<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaRecurso;
use App\Http\Requests\TamanhoRequest;
use App\Models\Tamanho;
use App\Services\ClienteService;
use App\Services\TamanhoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class TamanhoController extends Controller implements HasMiddleware
{
    use AutorizaRecurso;

    public function __construct(
        private readonly TamanhoService $tamanhos,
        private readonly ClienteService $clientes,
    ) {}

    public static function middleware(): array
    {
        return self::autorizacoes(Tamanho::class, 'tamanho');
    }

    public function index(Request $request): View
    {
        $filtros = $request->only('busca');

        return view('tamanhos.index', [
            'tamanhos' => $this->tamanhos->listar($filtros),
            'filtros' => $filtros,
        ]);
    }

    public function create(Request $request): View
    {
        return view('tamanhos.create', [
            'tamanho' => new Tamanho(['cliente_id' => $request->integer('cliente_id') ?: null]),
            'clientes' => $this->clientes->opcoes(),
        ]);
    }

    public function store(TamanhoRequest $request): RedirectResponse
    {
        $tamanho = $this->tamanhos->criar($request->validated());

        return redirect()->route('tamanhos.show', $tamanho)->with('success', 'Tamanhos cadastrados!');
    }

    public function show(Tamanho $tamanho): View
    {
        return view('tamanhos.show', ['tamanho' => $this->tamanhos->detalhar($tamanho)]);
    }

    public function edit(Tamanho $tamanho): View
    {
        return view('tamanhos.edit', [
            'tamanho' => $tamanho,
            'clientes' => $this->clientes->opcoes(),
        ]);
    }

    public function update(TamanhoRequest $request, Tamanho $tamanho): RedirectResponse
    {
        $this->tamanhos->atualizar($tamanho, $request->validated());

        return redirect()->route('tamanhos.show', $tamanho)->with('success', 'Tamanhos atualizados!');
    }

    public function destroy(Tamanho $tamanho): RedirectResponse
    {
        $this->tamanhos->excluir($tamanho);

        return redirect()->route('tamanhos.index')->with('success', 'Tamanhos removidos!');
    }
}
