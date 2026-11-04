<?php

use App\Http\Controllers\Api\MatriculaController;
use App\Http\Controllers\Api\PagamentoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API  (prefixo /api, definido em bootstrap/app.php)
|--------------------------------------------------------------------------
|
| Autenticação HTTP Basic (e-mail e senha de um usuário do sistema).
| Permissões iguais às do CRUD web: admin e instrutor leem/gravam,
| somente admin exclui; o papel "aluno" recebe 403.
|
|   Matrículas
|   GET    /api/matriculas              listagem paginada (filtros opcionais)
|   POST   /api/matriculas              cria
|   GET    /api/matriculas/{matricula}  detalha
|   PUT    /api/matriculas/{matricula}  atualiza
|   DELETE /api/matriculas/{matricula}  remove
|
|   Pagamentos
|   GET    /api/pagamentos              listagem paginada (busca, status, plano, ativo)
|   POST   /api/pagamentos              cria (201)
|   GET    /api/pagamentos/{pagamento}  detalha
|   PUT    /api/pagamentos/{pagamento}  atualiza
|   DELETE /api/pagamentos/{pagamento}  remove (204)
|
*/

Route::middleware('api.basic')
    ->name('api.')
    ->group(function () {
        Route::apiResource('matriculas', MatriculaController::class);
        Route::apiResource('pagamentos', PagamentoController::class);
    });
