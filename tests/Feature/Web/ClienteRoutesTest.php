<?php

namespace Tests\Feature\Web;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** Rotas protegidas por login; a equipe lista e filtra os clientes. */
    public function test_visitante_vai_para_o_login_e_a_equipe_lista_e_busca_clientes(): void
    {
        Cliente::factory()->create(['nome' => 'Ana Faixa Preta', 'cpf' => '111.111.111-11']);
        Cliente::factory()->create(['nome' => 'Bruno Iniciante', 'cpf' => '222.222.222-22']);

        $this->get(route('clientes.index'))->assertRedirect(route('login'));

        $instrutor = User::factory()->create(['role' => 'instrutor']);

        $this->actingAs($instrutor)
            ->get(route('clientes.index'))
            ->assertOk()
            ->assertSee('Ana Faixa Preta')
            ->assertSee('Bruno Iniciante');

        // A busca por parte do nome deixa só quem combina.
        $this->actingAs($instrutor)
            ->get(route('clientes.index', ['busca' => 'faixa pre']))
            ->assertOk()
            ->assertSee('Ana Faixa Preta')
            ->assertDontSee('Bruno Iniciante');
    }

    /** Cadastro com validação (Form Request) e controle de acesso por papel. */
    public function test_equipe_cadastra_cliente_validando_os_dados_e_aluno_e_bloqueado(): void
    {
        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $valido = [
            'nome' => 'Carla Souza',
            'idade' => 28,
            'cpf' => '333.333.333-33',
            'endereco' => 'Rua das Flores, 10 - Centro - Guarapuava/PR',
        ];

        // CPF fora do formato: volta ao formulário com erro e nada é gravado.
        $this->actingAs($instrutor)
            ->from(route('clientes.create'))
            ->post(route('clientes.store'), [...$valido, 'cpf' => '12345678900'])
            ->assertRedirect(route('clientes.create'))
            ->assertSessionHasErrors('cpf');
        $this->assertDatabaseCount('clientes', 0);

        // Dados válidos: grava e vai para a tela de detalhes.
        $this->actingAs($instrutor)
            ->post(route('clientes.store'), $valido)
            ->assertRedirect(route('clientes.show', Cliente::firstWhere('cpf', '333.333.333-33')));
        $this->assertDatabaseHas('clientes', ['nome' => 'Carla Souza', 'cpf' => '333.333.333-33']);

        // CPF repetido é recusado.
        $this->actingAs($instrutor)
            ->post(route('clientes.store'), $valido)
            ->assertSessionHasErrors('cpf');
        $this->assertDatabaseCount('clientes', 1);

        // Papel "aluno" não acessa nem cadastra (403 vem antes da validação).
        $aluno = User::factory()->create(['role' => 'aluno']);
        $this->actingAs($aluno)->get(route('clientes.create'))->assertForbidden();
        $this->actingAs($aluno)->post(route('clientes.store'), [])->assertForbidden();
    }
}
