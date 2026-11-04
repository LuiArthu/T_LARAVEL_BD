<?php

namespace Tests\Feature\Web;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentoRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** Rotas protegidas por login; a equipe vê a lista e o papel "aluno" é barrado. */
    public function test_visitante_vai_para_o_login_equipe_lista_e_aluno_e_bloqueado(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Ana Faixa Preta']);
        Pagamento::factory()->create([
            'cliente_id' => $cliente->id,
            'forma_pagamento' => 'Credito',
        ]);

        $this->get(route('pagamentos.index'))->assertRedirect(route('login'));

        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $this->actingAs($instrutor)
            ->get(route('pagamentos.index'))
            ->assertOk()
            ->assertSee('Pagamentos')
            ->assertSee('Ana Faixa Preta')
            ->assertSee('Crédito');

        $aluno = User::factory()->create(['role' => 'aluno']);
        $this->actingAs($aluno)->get(route('pagamentos.index'))->assertForbidden();
    }

    /** Cadastro com Form Request: dados inválidos voltam com erro, válidos gravam. */
    public function test_equipe_cadastra_pagamento_validando_os_dados(): void
    {
        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $cliente = Cliente::factory()->create();
        $valido = [
            'cliente_id' => $cliente->id,
            'dia_pagamento' => 15,
            'plano' => 'Mensal',
            'forma_pagamento' => 'Debito',
            'ativo' => 1,
            'status_pagamento' => 'Pago',
        ];

        // Dia fora de 1–28: volta ao formulário com erro e nada é gravado.
        $this->actingAs($instrutor)
            ->from(route('pagamentos.create'))
            ->post(route('pagamentos.store'), [...$valido, 'dia_pagamento' => 31])
            ->assertRedirect(route('pagamentos.create'))
            ->assertSessionHasErrors('dia_pagamento');
        $this->assertDatabaseCount('pagamento', 0);

        // Regra da trigger: pagamento inativo não pode ficar como "Pago".
        // (Checkbox desmarcado não é enviado, por isso "ativo" some do corpo.)
        $semAtivo = $valido;
        unset($semAtivo['ativo']);

        $this->actingAs($instrutor)
            ->from(route('pagamentos.create'))
            ->post(route('pagamentos.store'), $semAtivo)
            ->assertRedirect(route('pagamentos.create'))
            ->assertSessionHasErrors('status_pagamento');
        $this->assertDatabaseCount('pagamento', 0);

        // Dados válidos: grava e vai para a tela de detalhes.
        $this->actingAs($instrutor)
            ->post(route('pagamentos.store'), $valido)
            ->assertRedirect(route('pagamentos.show', Pagamento::firstWhere('cliente_id', $cliente->id)));
        $this->assertDatabaseHas('pagamento', [
            'cliente_id' => $cliente->id,
            'plano' => 'Mensal',
            'status_pagamento' => 'Pago',
        ]);

        // Papel "aluno" não cadastra (403 vem antes da validação).
        $aluno = User::factory()->create(['role' => 'aluno']);
        $this->actingAs($aluno)->post(route('pagamentos.store'), [])->assertForbidden();
    }

    /** Só o admin exclui; o instrutor recebe 403 e o pagamento continua lá. */
    public function test_somente_admin_exclui_pagamento(): void
    {
        $pagamento = Pagamento::factory()->create();

        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $this->actingAs($instrutor)
            ->delete(route('pagamentos.destroy', $pagamento))
            ->assertForbidden();
        $this->assertDatabaseHas('pagamento', ['id' => $pagamento->id]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->delete(route('pagamentos.destroy', $pagamento))
            ->assertRedirect(route('pagamentos.index'));
        $this->assertDatabaseMissing('pagamento', ['id' => $pagamento->id]);
    }
}
