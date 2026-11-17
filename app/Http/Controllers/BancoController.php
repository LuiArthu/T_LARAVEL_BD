<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use App\Models\Pagamento;
use App\Models\Quitacao;
use App\Services\BancoService;
use DomainException;
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
                'quitacoes' => $this->banco->ultimasQuitacoes(),
            ];
        }

        return view('banco.index', $dados);
    }

    /** CALL atualizar_pagamento('Devendo', plano, 'Pago') */
    public function quitarPlano(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'plano' => ['required', Rule::in(Pagamento::PLANOS)],
        ]);

        if (! $this->banco->disponivel()) {
            return back()->with('error', 'A procedure atualizar_pagamento só existe no PostgreSQL.');
        }

        $quitacao = $this->banco->quitarPlano($dados['plano']);

        if ($quitacao === null) {
            return back()->with('error', "Nenhum pagamento ativo do plano {$dados['plano']} está devendo: nada a quitar.");
        }

        return back()->with('success', "Procedure atualizar_pagamento executada: {$quitacao->quantidade} pagamento(s) do plano {$dados['plano']} passaram de Devendo para Pago. Para reverter, use \"Desfazer\" em Últimas quitações.");
    }

    /** Volta para 'Devendo' somente os pagamentos que esta quitação marcou como 'Pago'. */
    public function desfazerQuitacao(Quitacao $quitacao): RedirectResponse
    {
        try {
            $voltaram = $this->banco->desfazerQuitacao($quitacao);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Quitação #{$quitacao->id} desfeita: {$voltaram} pagamento(s) do plano {$quitacao->plano} voltaram para Devendo.");
    }
}
