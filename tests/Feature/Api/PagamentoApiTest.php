<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentoApiTest extends TestCase
{
    use RefreshDatabase;

    /** Cabeçalho HTTP Basic de verdade (a API não usa sessão; a senha da factory é "password"). */
    private function basic(User $usuario): array
    {
        return ['Authorization' => 'Basic '.base64_encode($usuario->email.':password')];
    }

    /** 401 sem credenciais, 403 para o papel "aluno" e 200 para a equipe. */
    public function test_api_exige_autenticacao_e_so_a_equipe_acessa(): void
    {
        $this->getJson('/api/pagamentos')->assertUnauthorized();

        $aluno = User::factory()->create(['role' => 'aluno']);
        $this->withHeaders($this->basic($aluno))
            ->getJson('/api/pagamentos')
            ->assertForbidden();

        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $this->withHeaders($this->basic($instrutor))
            ->getJson('/api/pagamentos')
            ->assertOk();
    }

    /** Lista paginada em JSON, com o nome do cliente e sem expor o CPF. */
    public function test_api_lista_pagamentos_em_json_e_filtra_por_situacao(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cliente = Cliente::factory()->create(['nome' => 'Ana Faixa Preta']);

        Pagamento::factory()->create([
            'cliente_id' => $cliente->id,
            'forma_pagamento' => 'Credito',
            'status_pagamento' => 'Pago',
        ]);
        Pagamento::factory()->devendo()->create();

        $resposta = $this->withHeaders($this->basic($admin))->getJson('/api/pagamentos');

        $resposta->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'cliente_id',
                    'dia_pagamento',
                    'plano',
                    'forma_pagamento',
                    'ativo',
                    'status_pagamento',
                    'cliente' => ['id', 'nome'],
                ]],
                'links',
                'meta',
            ])
            ->assertJsonMissingPath('data.0.cliente.cpf');

        // Filtro por situação deixa só quem está devendo.
        $this->withHeaders($this->basic($admin))
            ->getJson('/api/pagamentos?status=Devendo')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status_pagamento', 'Devendo');

        // Busca pelo nome do cliente.
        $this->withHeaders($this->basic($admin))
            ->getJson('/api/pagamentos?busca=faixa')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.cliente.nome', 'Ana Faixa Preta');
    }

    /** Cria (201), detalha, atualiza e exclui (204), com as permissões por papel. */
    public function test_ciclo_completo_de_pagamentos_pela_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $cliente = Cliente::factory()->create();

        $dados = [
            'cliente_id' => $cliente->id,
            'dia_pagamento' => 10,
            'plano' => 'Mensal',
            'forma_pagamento' => 'Credito',
            'ativo' => true,
            'status_pagamento' => 'Pago',
        ];

        $criado = $this->withHeaders($this->basic($instrutor))
            ->postJson('/api/pagamentos', $dados)
            ->assertCreated()
            ->assertJsonPath('data.plano', 'Mensal')
            ->assertJsonPath('data.dia_pagamento', 10)
            ->assertJsonPath('data.cliente.nome', $cliente->nome);

        $id = $criado->json('data.id');
        $this->assertDatabaseHas('pagamento', ['id' => $id, 'cliente_id' => $cliente->id, 'status_pagamento' => 'Pago']);

        $this->withHeaders($this->basic($instrutor))
            ->getJson("/api/pagamentos/{$id}")
            ->assertOk()
            ->assertJsonPath('data.forma_pagamento', 'Credito');

        $this->withHeaders($this->basic($instrutor))
            ->putJson("/api/pagamentos/{$id}", [...$dados, 'status_pagamento' => 'Devendo'])
            ->assertOk()
            ->assertJsonPath('data.status_pagamento', 'Devendo');
        $this->assertDatabaseHas('pagamento', ['id' => $id, 'status_pagamento' => 'Devendo']);

        // Excluir é só do admin.
        $this->withHeaders($this->basic($instrutor))
            ->deleteJson("/api/pagamentos/{$id}")
            ->assertForbidden();
        $this->assertDatabaseHas('pagamento', ['id' => $id]);

        $this->withHeaders($this->basic($admin))
            ->deleteJson("/api/pagamentos/{$id}")
            ->assertNoContent();
        $this->assertDatabaseMissing('pagamento', ['id' => $id]);
    }

    /** Regras de validação, incluindo a da trigger: inativo não pode ficar "Pago". */
    public function test_api_valida_os_dados_do_pagamento(): void
    {
        $instrutor = User::factory()->create(['role' => 'instrutor']);
        $cliente = Cliente::factory()->create();
        $valido = [
            'cliente_id' => $cliente->id,
            'dia_pagamento' => 10,
            'plano' => 'Anual',
            'forma_pagamento' => 'Debito',
            'ativo' => true,
            'status_pagamento' => 'Pago',
        ];
        $auth = $this->basic($instrutor);

        $this->withHeaders($auth)->postJson('/api/pagamentos', [...$valido, 'dia_pagamento' => 29])
            ->assertUnprocessable()->assertJsonValidationErrors('dia_pagamento');

        $this->withHeaders($auth)->postJson('/api/pagamentos', [...$valido, 'cliente_id' => 999999])
            ->assertUnprocessable()->assertJsonValidationErrors('cliente_id');

        $this->withHeaders($auth)->postJson('/api/pagamentos', [...$valido, 'forma_pagamento' => 'Pix'])
            ->assertUnprocessable()->assertJsonValidationErrors('forma_pagamento');

        $this->withHeaders($auth)->postJson('/api/pagamentos', [...$valido, 'ativo' => false])
            ->assertUnprocessable()->assertJsonValidationErrors('status_pagamento');

        // Inativo e devendo é permitido.
        $this->withHeaders($auth)
            ->postJson('/api/pagamentos', [...$valido, 'ativo' => false, 'status_pagamento' => 'Devendo'])
            ->assertCreated();

        $this->assertDatabaseCount('pagamento', 1);
    }
}
