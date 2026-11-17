<?php

use App\Http\Controllers\BancoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\TamanhoController;
use App\Http\Controllers\ProfileController;
use App\Models\Cliente;
use App\Models\Matricula;
use App\Models\Modalidade;
use App\Models\Pagamento;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// A página inicial leva direto ao sistema: quem não está logado cai no login.
Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {

    // Clientes têm CPF e situação financeira: só a equipe vê números e nomes.
    $equipe = auth()->user()->can('viewAny', Cliente::class);

    $totalClientes = $equipe ? Cliente::count() : null;
    $totalModalidades = Modalidade::count();

    $clientesRecentes = $equipe
        ? Cliente::with('matriculas:id,cliente_id,modalidade')->latest('id')->take(5)->get()
        : collect();

    return view('dashboard', compact(
        'equipe',
        'totalClientes',
        'totalModalidades',
        'clientesRecentes'
    ));

})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Perfil
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Alunos -> Clientes
    |--------------------------------------------------------------------------
    |
    | "Aluno" e "cliente" são a mesma pessoa, cadastrada uma única vez em
    | clientes. O endereço antigo continua funcionando.
    |
    */

    Route::redirect('/alunos', '/clientes', 301);


    /*
    |--------------------------------------------------------------------------
    | Clientes
    |--------------------------------------------------------------------------
    |
    | CRUD completo. Leitura e escrita para admin/instrutor; exclusão só admin
    | (ver App\Policies\StaffPolicy).
    |
    */

    Route::resource('clientes', ClienteController::class);


    /*
    |--------------------------------------------------------------------------
    | Matrículas
    |--------------------------------------------------------------------------
    |
    | GET    /matriculas                    matriculas.index
    | GET    /matriculas/create             matriculas.create
    | POST   /matriculas                    matriculas.store
    | GET    /matriculas/{matricula}        matriculas.show
    | GET    /matriculas/{matricula}/edit   matriculas.edit
    | PUT    /matriculas/{matricula}        matriculas.update
    | DELETE /matriculas/{matricula}        matriculas.destroy
    |
    | A versão JSON destas rotas está em routes/api.php (/api/matriculas).
    |
    */

    Route::resource('matriculas', MatriculaController::class);


    /*
    |--------------------------------------------------------------------------
    | Tamanhos (faixa e kimono)
    |--------------------------------------------------------------------------
    */

    Route::resource('tamanhos', TamanhoController::class);


    /*
    |--------------------------------------------------------------------------
    | Pagamentos
    |--------------------------------------------------------------------------
    */

    Route::resource('pagamentos', PagamentoController::class);


    /*
    |--------------------------------------------------------------------------
    | Banco de dados (functions, procedure e trigger do PostgreSQL)
    |--------------------------------------------------------------------------
    |
    | GET  /banco               resultado das functions + histórico do trigger
    | POST /banco/quitar-plano  executa a procedure atualizar_pagamento (admin)
    | POST /banco/quitacoes/{quitacao}/desfazer  desfaz uma quitação (admin)
    |
    */

    Route::get('/banco', [BancoController::class, 'index'])
        ->middleware('can:viewAny,'.Pagamento::class)
        ->name('banco.index');

    Route::post('/banco/quitar-plano', [BancoController::class, 'quitarPlano'])
        ->middleware('admin')
        ->name('banco.quitar-plano');

    Route::post('/banco/quitacoes/{quitacao}/desfazer', [BancoController::class, 'desfazerQuitacao'])
        ->middleware('admin')
        ->name('banco.desfazer-quitacao');


    /*
    |--------------------------------------------------------------------------
    | Modalidades
    |--------------------------------------------------------------------------
    */

    Route::get('/modalidades', function () {

        // Matrículas por modalidade. O nome em `modalidades` pode ter acento
        // (Judô) e na matrícula não (Judo), então comparamos sem acento.
        $matriculados = Matricula::selectRaw('modalidade, COUNT(*) AS total')
            ->groupBy('modalidade')
            ->pluck('total', 'modalidade');

        $modalidades = Modalidade::orderBy('nome')->get()->each(
            fn (Modalidade $m) => $m->setAttribute(
                'matriculas_count',
                (int) ($matriculados[Str::ascii($m->nome)] ?? 0)
            )
        );

        return view('modalidades.index', compact('modalidades'));

    })->name('modalidades.index');


    /*
    |--------------------------------------------------------------------------
    | Área administrativa
    |--------------------------------------------------------------------------
    |
    | Mantemos a rota para não quebrar o que já existia.
    | O administrador será direcionado ao dashboard principal.
    |
    */

    Route::middleware(['admin'])
        ->prefix('admin')
        ->group(function () {

            Route::get('/dashboard', function () {
                return redirect()->route('dashboard');
            })->name('admin.dashboard');

        });

});


require __DIR__.'/auth.php';
