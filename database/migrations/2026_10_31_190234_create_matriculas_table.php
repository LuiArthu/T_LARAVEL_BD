<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->string('modalidade', 20);
            $table->string('faixa', 20);
            $table->date('data_inicio');

            $table->foreign('cliente_id')->references('id')->on('clientes');

            // Mesmo índice do script SQL (seção 3.1).
            $table->index(['cliente_id', 'modalidade'], 'idx_matriculas_cliente_modalidade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
