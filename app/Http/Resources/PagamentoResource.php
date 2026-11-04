<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Pagamento */
class PagamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cliente_id' => $this->cliente_id,
            'dia_pagamento' => $this->dia_pagamento,
            'plano' => $this->plano,
            'forma_pagamento' => $this->forma_pagamento,
            'ativo' => $this->ativo,
            'status_pagamento' => $this->status_pagamento,
            // Só id e nome: CPF e endereço não saem pela API de pagamentos.
            'cliente' => $this->whenLoaded('cliente', fn () => [
                'id' => $this->cliente->id,
                'nome' => $this->cliente->nome,
            ]),
        ];
    }
}
