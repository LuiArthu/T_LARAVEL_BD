<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory;

    /** No script SQL a tabela é singular ("pagamento"). */
    protected $table = 'pagamento';

    public $timestamps = false;

    public const PLANOS = ['Mensal', 'Anual'];

    public const FORMAS = ['Credito', 'Debito'];

    public const STATUS = ['Pago', 'Devendo'];

    protected $fillable = [
        'cliente_id', 'dia_pagamento', 'plano', 'forma_pagamento', 'ativo', 'status_pagamento',
    ];

    protected function casts(): array
    {
        return [
            'dia_pagamento' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    /** Mesmo critério da function SQL fn_inadimplentes(): ativo e devendo. */
    public function scopeInadimplentes(Builder $query): Builder
    {
        return $query->where('ativo', true)->where('status_pagamento', 'Devendo');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
