# Rotas de pagamentos

Todas exigem login (web) ou HTTP Basic (API). Permissões, iguais nas duas:
**admin** e **instrutor** consultam, cadastram e editam; só o **admin** exclui;
o papel **aluno** recebe 403.

## Rotas web

As rotas web ficam dentro do middleware `auth`.

```php
Route::resource('pagamentos', PagamentoController::class);
```

| Método | Rota | O que faz |
|---|---|---|
| GET | `/pagamentos` | Lista com busca por cliente (nome/CPF) e filtros de situação e ativo. |
| GET | `/pagamentos/create` | Abre o cadastro. |
| POST | `/pagamentos` | Salva um pagamento. |
| GET | `/pagamentos/{pagamento}` | Mostra os detalhes. |
| GET | `/pagamentos/{pagamento}/edit` | Abre a edição. |
| PUT/PATCH | `/pagamentos/{pagamento}` | Atualiza o pagamento. |
| DELETE | `/pagamentos/{pagamento}` | Exclui o pagamento (só admin). |

## Rotas da API

Em `routes/api.php`, no grupo `api.basic` (e-mail e senha de um usuário).

```php
Route::apiResource('pagamentos', PagamentoController::class);
```

| Método | Rota | O que retorna |
|---|---|---|
| GET | `/api/pagamentos` | Lista em JSON, paginada (`?busca=&status=&plano=&ativo=&per_page=`). |
| POST | `/api/pagamentos` | Cria e retorna HTTP 201. |
| GET | `/api/pagamentos/{pagamento}` | Mostra um pagamento em JSON. |
| PUT/PATCH | `/api/pagamentos/{pagamento}` | Atualiza e retorna o pagamento. |
| DELETE | `/api/pagamentos/{pagamento}` | Exclui e retorna HTTP 204 (só admin). |

Sem credenciais a API responde 401; dados inválidos, 422.

## Exemplo de JSON

Envio (`POST /api/pagamentos`):

```json
{
  "cliente_id": 1,
  "dia_pagamento": 10,
  "plano": "Mensal",
  "forma_pagamento": "Credito",
  "status_pagamento": "Pago",
  "ativo": true
}
```

Resposta (`201`):

```json
{
  "data": {
    "id": 501,
    "cliente_id": 1,
    "dia_pagamento": 10,
    "plano": "Mensal",
    "forma_pagamento": "Credito",
    "ativo": true,
    "status_pagamento": "Pago",
    "cliente": { "id": 1, "nome": "Nome do cliente" }
  }
}
```

## Valores aceitos

- `plano`: `Mensal` ou `Anual`.
- `forma_pagamento`: `Credito` ou `Debito`.
- `status_pagamento`: `Pago` ou `Devendo`.
- `dia_pagamento`: número de 1 a 28.
- Um pagamento com `ativo: false` não pode ter `status_pagamento: "Pago"`
  (regra da trigger `trg_bloqueio_pagamento`, também validada pelo app).
