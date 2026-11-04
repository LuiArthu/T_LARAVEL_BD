<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamento', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cliente_id');
            $table->smallInteger('dia_pagamento');
            $table->string('plano', 10);
            $table->string('forma_pagamento', 10);
            $table->boolean('ativo');
            $table->string('status_pagamento', 10);

            $table->foreign('cliente_id')->references('id')->on('clientes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamento');
    }
};
