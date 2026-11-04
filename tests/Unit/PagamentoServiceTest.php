<?php

namespace Tests\Unit;

use App\Services\PagamentoService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PagamentoServiceTest extends TestCase
{
    /**
     * atualizarStatus() valida os argumentos ANTES de chamar a procedure
     * atualizar_pagamento (que só existe no PostgreSQL). Por isso estes casos
     * rodam sem banco; a chamada à procedure em si é coberta pelo script SQL.
     */
    public function test_atualizar_status_recusa_status_atual_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status atual inválido.');

        (new PagamentoService)->atualizarStatus('Cancelado', 'Anual', 'Pago');
    }

    public function test_atualizar_status_recusa_novo_status_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Novo status inválido.');

        (new PagamentoService)->atualizarStatus('Devendo', 'Anual', 'Quitado');
    }

    public function test_atualizar_status_recusa_plano_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Plano inválido.');

        (new PagamentoService)->atualizarStatus('Devendo', 'Semestral', 'Pago');
    }
}
