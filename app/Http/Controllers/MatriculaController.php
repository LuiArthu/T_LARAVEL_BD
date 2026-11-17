<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AutorizaRecurso;
use App\Http\Requests\MatriculaRequest;
use App\Models\Matricula;
use App\Services\ClienteService;
use App\Services\MatriculaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class MatriculaController extends Controller implements HasMiddleware
{
    use AutorizaRecurso;

    public function __construct(
        private readonly MatriculaService $matriculas,
        private readonly ClienteService $clientes,
    ) {}

    public static function middleware(): array
    {
        return self::autorizacoes(Matricula::class, 'matricula');
    }

    public function index(Request $request): View
    {
        $filtros = $request->only(['busca', 'modalidade', 'faixa']);

        return view('matriculas.index', [
            'matriculas' => $this->matriculas->listar($filtros),
            'filtros' => $filtros,
        ]);
    }

    public function create(Request $request): View
    {
        $matricula = new Matricula(['cliente_id' => $request->integer('cliente_id') ?: null]);

        return view('matriculas.create', [
            'matricula' => $matricula,
            'clientes' => $this->clientes->opcoes(),
        ]);
    }

    public function store(MatriculaRequest $request): RedirectResponse
    {
        $matricula = $this->matriculas->criar($request->validated());

        return redirect()->route('matriculas.show', $matricula)->with('success', 'Matrícula cadastrada!');
    }

    public function show(Matricula $matricula): View
    {
        return view('matriculas.show', ['matricula' => $this->matriculas->detalhar($matricula)]);
    }

    public function edit(Matricula $matricula): View
    {
        return view('matriculas.edit', [
            'matricula' => $matricula,
            'clientes' => $this->clientes->opcoes(),
        ]);
    }

    public function update(MatriculaRequest $request, Matricula $matricula): RedirectResponse
    {
        $this->matriculas->atualizar($matricula, $request->validated());

        return redirect()->route('matriculas.show', $matricula)->with('success', 'Matrícula atualizada!');
    }

    public function destroy(Matricula $matricula): RedirectResponse
    {
        $this->matriculas->excluir($matricula);

        return redirect()->route('matriculas.index')->with('success', 'Matrícula removida!');
    }
}
