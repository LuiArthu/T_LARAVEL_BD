<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Uma execução da procedure atualizar_pagamento feita pela tela "Banco de dados". */
class Quitacao extends Model
{
    protected $table = 'quitacoes';

    protected $fillable = ['plano', 'quantidade', 'user_id', 'desfeita_em'];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'desfeita_em' => 'datetime',
        ];
    }

    public function foiDesfeita(): bool
    {
        return $this->desfeita_em !== null;
    }

    /** Os pagamentos que esta quitação passou de Devendo para Pago. */
    public function pagamentos(): BelongsToMany
    {
        return $this->belongsToMany(Pagamento::class, 'quitacao_pagamento', 'quitacao_id', 'pagamento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
