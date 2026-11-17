<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Pagamento;
use App\Services\BancoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Tela "Banco de dados": mostra o resultado das functions, executa a
 * procedure e lista o histórico gravado pelo trigger, tudo no PostgreSQL.
 */
class BancoController extends Controller
{
    public function __construct(private readonly BancoService $banco) {}

    /** GET /banco?modalidade=&plano=&valor= */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'modalidade' => ['nullable', Rule::in(Matricula::MODALIDADES)],
            'plano' => ['nullable', Rule::in(Pagamento::PLANOS)],
            'valor' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        $modalidade = $filtros['modalidade'] ?? null;
        $plano = $filtros['plano'] ?? 'Mensal';
        $valor = (int) ($filtros['valor'] ?? 150);

        $dados = compact('modalidade', 'plano', 'valor') + ['disponivel' => $this->banco->disponivel()];

        if ($dados['disponivel']) {
            $dados += [
                'inadimplentes' => $this->banco->inadimplentes($modalidade),
                'mensalidadesPagas' => $this->banco->totalMensalidadesPagas($modalidade ?? 'Jiu-Jitsu', $plano),
                'faixasPretas' => $this->banco->totalFaixasPretasComDesconto($valor),
                'historico' => $this->banco->historicoFaixas(),
            ];
        }

        return view('banco.index', $dados);
    }

    /** POST /banco/quitar-plano  (somente admin): CALL atualizar_pagamento('Devendo', plano, 'Pago') */
    public function quitarPlano(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'plano' => ['required', Rule::in(Pagamento::PLANOS)],
        ]);

        if (! $this->banco->disponivel()) {
            return back()->with('error', 'A procedure atualizar_pagamento só existe no PostgreSQL.');
        }

        $alterados = $this->banco->quitarPlano($dados['plano']);

        return back()->with('success', "Procedure atualizar_pagamento executada: {$alterados} pagamento(s) do plano {$dados['plano']} passaram de Devendo para Pago.");
    }
}
