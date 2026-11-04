<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AutorizaRecurso;
use App\Http\Controllers\Controller;
use App\Http\Requests\PagamentoRequest;
use App\Http\Resources\PagamentoResource;
use App\Models\Pagamento;
use App\Services\PagamentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;

class PagamentoController extends Controller implements HasMiddleware
{
    use AutorizaRecurso;

    public function __construct(private readonly PagamentoService $pagamentos) {}

    public static function middleware(): array
    {
        return self::autorizacoes(Pagamento::class, 'pagamento');
    }

    /** GET /api/pagamentos?busca=&status=&plano=&ativo=&per_page= */
    public function index(Request $request): AnonymousResourceCollection
    {
        $porPagina = min(max($request->integer('per_page', 15), 1), 100);

        return PagamentoResource::collection(
            $this->pagamentos->listar(
                $request->only(['busca', 'status', 'plano', 'ativo']),
                $porPagina,
            )
        );
    }

    /** POST /api/pagamentos */
    public function store(PagamentoRequest $request): JsonResponse
    {
        $pagamento = $this->pagamentos->criar($request->validated());

        return (new PagamentoResource($pagamento->load('cliente')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /** GET /api/pagamentos/{pagamento} */
    public function show(Pagamento $pagamento): PagamentoResource
    {
        return new PagamentoResource($this->pagamentos->detalhar($pagamento));
    }

    /** PUT|PATCH /api/pagamentos/{pagamento} */
    public function update(PagamentoRequest $request, Pagamento $pagamento): PagamentoResource
    {
        $this->pagamentos->atualizar($pagamento, $request->validated());

        return new PagamentoResource($pagamento->load('cliente'));
    }

    /** DELETE /api/pagamentos/{pagamento} */
    public function destroy(Pagamento $pagamento): Response
    {
        $this->pagamentos->excluir($pagamento);

        return response()->noContent();
    }
}
