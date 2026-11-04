<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Matricula;
use Illuminate\Database\Seeder;

/** Substitui o antigo AlunoSeeder: as mesmas pessoas, agora como clientes com matrícula. */
class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $pessoas = [
            ['João Silva', '111.111.111-11', 12, 'Rua A, 100, Guarapuava', 'Jiu-Jitsu', 'Branca'],
            ['Maria Souza', '222.222.222-22', 25, 'Rua B, 200, Guarapuava', 'Judo', 'Branca'],
            ['Pedro Santos', '333.333.333-33', 8, 'Rua C, 300, Guarapuava', 'Muay Thai', Matricula::FAIXA_NAO_SE_APLICA],
        ];

        foreach ($pessoas as [$nome, $cpf, $idade, $endereco, $modalidade, $faixa]) {
            $cliente = Cliente::create(compact('nome', 'cpf', 'idade', 'endereco'));

            $cliente->matriculas()->create([
                'modalidade' => $modalidade,
                'faixa' => $faixa,
                'data_inicio' => now()->toDateString(),
            ]);
        }
    }
}
