<?php

namespace Tests\Feature\Web;

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
}
