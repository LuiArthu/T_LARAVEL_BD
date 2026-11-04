<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Faz a pessoa existir uma única vez: "aluno" passa a ser o próprio cliente.
 *
 * Para cada linha de `alunos`:
 *   1. o cliente é localizado pelo CPF; se não existir, é criado;
 *      (se já existir, os dados do cliente são mantidos: ele é a fonte oficial)
 *   2. a modalidade que o aluno tinha vira uma matrícula desse cliente,
 *      sem duplicar caso o cliente já esteja matriculado nela;
 * e só então a tabela `alunos` é removida.
 */
return new class extends Migration
{
    /** Cópia local de Matricula::MODALIDADES: migration não deve depender do código do app. */
    private const MODALIDADES = ['Jiu-Jitsu', 'Judo', 'Muay Thai', 'Karate', 'Boxe', 'Personal'];

    private const SEM_FAIXA = ['Boxe', 'Muay Thai', 'Personal'];

    public function up(): void
    {
        if (! Schema::hasTable('alunos')) {
            return;
        }

        DB::transaction(function () {
            $modalidades = Schema::hasTable('modalidades')
                ? DB::table('modalidades')->pluck('nome', 'id')
                : collect();

            foreach (DB::table('alunos')->orderBy('id')->get() as $aluno) {
                $clienteId = DB::table('clientes')->where('cpf', $aluno->cpf)->value('id')
                    ?? DB::table('clientes')->insertGetId([
                        'nome' => $aluno->nome,
                        'idade' => $aluno->idade,
                        'cpf' => $aluno->cpf,
                        'endereco' => $aluno->endereco,
                    ]);

                $modalidade = $this->modalidadeDaMatricula($modalidades[$aluno->modalidade_id] ?? null);

                if ($modalidade === null) {
                    continue; // nome de modalidade desconhecido: o cliente fica, sem matrícula
                }

                $jaMatriculado = DB::table('matriculas')
                    ->where('cliente_id', $clienteId)
                    ->where('modalidade', $modalidade)
                    ->exists();

                if (! $jaMatriculado) {
                    DB::table('matriculas')->insert([
                        'cliente_id' => $clienteId,
                        'modalidade' => $modalidade,
                        // O aluno não guardava faixa: modalidades com graduação começam em Branca.
                        'faixa' => in_array($modalidade, self::SEM_FAIXA, true) ? 'N/A' : 'Branca',
                        'data_inicio' => substr((string) ($aluno->created_at ?? now()), 0, 10),
                    ]);
                }
            }
        });

        Schema::drop('alunos');
    }

    /**
     * Estrutura de volta, SEM os dados: depois do up() eles já estão em clientes/matriculas.
     */
    public function down(): void
    {
        Schema::create('alunos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf')->unique();
            $table->unsignedTinyInteger('idade');
            $table->string('endereco');
            $table->foreignId('modalidade_id')->constrained('modalidades');
            $table->timestamps();
        });
    }

    /** "Jiu-Jítsu" -> "Jiu-Jitsu", "Judô" -> "Judo": compara sem acento nem caixa. */
    private function modalidadeDaMatricula(?string $nome): ?string
    {
        if ($nome === null) {
            return null;
        }

        foreach (self::MODALIDADES as $valida) {
            if (Str::lower(Str::ascii($nome)) === Str::lower($valida)) {
                return $valida;
            }
        }

        return null;
    }
};
