<?php

namespace Tests\Feature\Api;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatriculaApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Percorre a API inteira com HTTP Basic de verdade (sem actingAs):
     * 401 sem credenciais, criar, regras de negócio, listar, detalhar,
     * atualizar e excluir.
     */
    public function test_ciclo_completo_de_matriculas_pela_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cliente = Cliente::factory()->create();
        $auth = ['Authorization' => 'Basic '.base64_encode($admin->email.':password')];

        // Sem credenciais ou com senha errada: 401 em JSON.
        $this->getJson('/api/matriculas')->assertUnauthorized();
        $this->withHeaders(['Authorization' => 'Basic '.base64_encode($admin->email.':senha-errada')])
            ->getJson('/api/matriculas')
            ->assertUnauthorized();

        // Credenciais corretas, mas papel "aluno": 403 (a equipe é quem acessa).
        $aluno = User::factory()->create(['role' => 'aluno']);
        $this->withHeaders(['Authorization' => 'Basic '.base64_encode($aluno->email.':password')])
            ->getJson('/api/matriculas')
            ->assertForbidden();

        // Cria. Boxe não tem graduação: a faixa enviada ("Preta") vira "N/A".
        $criada = $this->withHeaders($auth)->postJson('/api/matriculas', [
            'cliente_id' => $cliente->id,
            'modalidade' => 'Boxe',
            'faixa' => 'Preta',
            'data_inicio' => '2026-11-02',
        ])
            ->assertCreated()
            ->assertJsonPath('data.faixa', 'N/A')
            ->assertJsonPath('data.data_inicio', '2026-11-02')
            ->assertJsonPath('data.cliente.nome', $cliente->nome);

        $id = $criada->json('data.id');
        $this->assertDatabaseHas('matriculas', ['id' => $id, 'modalidade' => 'Boxe', 'faixa' => 'N/A']);

        // Regra: o mesmo cliente não pode ter duas matrículas na mesma modalidade.
        $this->withHeaders($auth)->postJson('/api/matriculas', [
            'cliente_id' => $cliente->id,
            'modalidade' => 'Boxe',
            'faixa' => 'N/A',
            'data_inicio' => '2026-11-03',
        ])->assertUnprocessable()->assertJsonValidationErrors('modalidade');

        // Regra: modalidade com graduação não aceita "N/A".
        $this->withHeaders($auth)->postJson('/api/matriculas', [
            'cliente_id' => $cliente->id,
            'modalidade' => 'Judo',
            'faixa' => 'N/A',
            'data_inicio' => '2026-11-03',
        ])->assertUnprocessable()->assertJsonValidationErrors('faixa');

        // Listagem filtrada e detalhe.
        $this->withHeaders($auth)->getJson('/api/matriculas?modalidade=Boxe')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id);

        $this->withHeaders($auth)->getJson("/api/matriculas/{$id}")
            ->assertOk()
            ->assertJsonPath('data.modalidade', 'Boxe');

        // Atualiza: passa para Karatê com faixa.
        $this->withHeaders($auth)->putJson("/api/matriculas/{$id}", [
            'cliente_id' => $cliente->id,
            'modalidade' => 'Karate',
            'faixa' => 'Verde',
            'data_inicio' => '2026-11-02',
        ])->assertOk()->assertJsonPath('data.faixa', 'Verde');

        // Exclui (admin) e confirma.
        $this->withHeaders($auth)->deleteJson("/api/matriculas/{$id}")->assertNoContent();
        $this->assertDatabaseMissing('matriculas', ['id' => $id]);
    }
}
