<?php

namespace Tests\Feature\Web;

use App\Models\Pagamento;
use App\Models\Quitacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tela "Banco de dados" (functions, procedure e trigger do PostgreSQL).
 *
 * Os testes rodam em SQLite em memória, onde esses objetos não existem:
 * aqui se verifica o acesso, a validação e o aviso. O resultado das
 * functions e da procedure é demonstrado no Neon (PostgreSQL).
 */
class BancoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_somente_a_equipe_ve_a_tela_do_banco(): void
    {
        $this->get(route('banco.index'))->assertRedirect(route('login'));

        $aluno = User::factory()->create(['role' => 'aluno']);
        $this->actingAs($aluno)->get(route('banco.index'))->assertForbidden();

        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $this->actingAs($instrutor)
            ->get(route('banco.index'))
            ->assertOk()
            ->assertSee('Banco de dados')
            ->assertSee('só existem no PostgreSQL');
    }

    public function test_somente_admin_executa_a_procedure_e_o_plano_e_validado(): void
    {
        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $this->actingAs($instrutor)
            ->post(route('banco.quitar-plano'), ['plano' => 'Anual'])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->from(route('banco.index'))
            ->post(route('banco.quitar-plano'), ['plano' => 'Semestral'])
            ->assertRedirect(route('banco.index'))
            ->assertSessionHasErrors('plano');

        // Fora do PostgreSQL a procedure não existe: volta com aviso, sem erro 500.
        $this->actingAs($admin)
            ->from(route('banco.index'))
            ->post(route('banco.quitar-plano'), ['plano' => 'Anual'])
            ->assertRedirect(route('banco.index'))
            ->assertSessionHas('error');
    }

    /**
     * Desfazer volta para Devendo SÓ os pagamentos da quitação; quem já
     * estava pago antes continua pago. Não dá para desfazer duas vezes.
     */
    public function test_admin_desfaz_quitacao_sem_mexer_em_quem_ja_estava_pago(): void
    {
        $jaEstavaPago = Pagamento::factory()->create(['plano' => 'Anual', 'status_pagamento' => 'Pago']);
        $quitado = Pagamento::factory()->create(['plano' => 'Anual', 'status_pagamento' => 'Pago']);

        $admin = User::factory()->create(['role' => 'admin']);
        $quitacao = Quitacao::create(['plano' => 'Anual', 'quantidade' => 1, 'user_id' => $admin->id]);
        $quitacao->pagamentos()->attach($quitado->id);

        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $this->actingAs($instrutor)
            ->post(route('banco.desfazer-quitacao', $quitacao))
            ->assertForbidden();

        $this->actingAs($admin)
            ->from(route('banco.index'))
            ->post(route('banco.desfazer-quitacao', $quitacao))
            ->assertRedirect(route('banco.index'))
            ->assertSessionHas('success');

        $this->assertSame('Devendo', $quitado->fresh()->status_pagamento);
        $this->assertSame('Pago', $jaEstavaPago->fresh()->status_pagamento);
        $this->assertNotNull($quitacao->fresh()->desfeita_em);

        // Segunda vez: recusado, nada muda.
        $this->actingAs($admin)
            ->from(route('banco.index'))
            ->post(route('banco.desfazer-quitacao', $quitacao))
            ->assertSessionHas('error');
    }
}
