<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AutorizaRecurso;
use App\Http\Controllers\Controller;
use App\Http\Requests\MatriculaRequest;
use App\Http\Resources\MatriculaResource;
use App\Models\Matricula;
use App\Services\MatriculaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;

class MatriculaController extends Controller implements HasMiddleware
{
    use AutorizaRecurso;

    public function __construct(private readonly MatriculaService $matriculas) {}

    public static function middleware(): array
    {
        return self::autorizacoes(Matricula::class, 'matricula');
    }

    /** GET /api/matriculas?busca=&modalidade=&faixa=&cliente_id=&per_page= */
    public function index(Request $request): AnonymousResourceCollection
    {
        $porPagina = min(max($request->integer('per_page', 15), 1), 100);

        return MatriculaResource::collection(
            $this->matriculas->listar(
                $request->only(['busca', 'modalidade', 'faixa', 'cliente_id']),
                $porPagina,
            )
        );
    }

    /** POST /api/matriculas */
    public function store(MatriculaRequest $request): JsonResponse
    {
        $matricula = $this->matriculas->criar($request->validated());

        return (new MatriculaResource($matricula->load('cliente')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /** GET /api/matriculas/{matricula} */
    public function show(Matricula $matricula): MatriculaResource
    {
        return new MatriculaResource($this->matriculas->detalhar($matricula));
    }

    /** PUT|PATCH /api/matriculas/{matricula} */
    public function update(MatriculaRequest $request, Matricula $matricula): MatriculaResource
    {
        $this->matriculas->atualizar($matricula, $request->validated());

        return new MatriculaResource($matricula->load('cliente'));
    }

    /** DELETE /api/matriculas/{matricula} */
    public function destroy(Matricula $matricula): Response
    {
        $this->matriculas->excluir($matricula);

        return response()->noContent();
    }
}
