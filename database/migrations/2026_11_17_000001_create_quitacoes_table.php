<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de cada execução da procedure atualizar_pagamento pela tela
 * "Banco de dados", com a lista exata dos pagamentos que ela alterou.
 * É o que permite DESFAZER uma quitação sem mexer em quem já estava pago.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quitacoes', function (Blueprint $table) {
            $table->id();
            $table->string('plano', 10);
            $table->unsignedInteger('quantidade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('desfeita_em')->nullable();
            $table->timestamps();
        });

        Schema::create('quitacao_pagamento', function (Blueprint $table) {
            $table->foreignId('quitacao_id')->constrained('quitacoes')->cascadeOnDelete();
            // Sem chave estrangeira para "pagamento" de propósito: o script
            // database/sql/academia_completo.sql recria essa tabela com
            // DROP TABLE, e uma FK aqui impediria o script de rodar.
            $table->unsignedInteger('pagamento_id')->index();
            $table->primary(['quitacao_id', 'pagamento_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quitacao_pagamento');
        Schema::dropIfExists('quitacoes');
    }
};
