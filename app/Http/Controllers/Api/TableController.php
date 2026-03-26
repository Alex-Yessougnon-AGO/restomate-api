<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\UpdateTableRequest;
use App\Models\Restaurant;
use App\Models\Table;
use App\Services\AvailabilityService;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AvailabilityService $availabilityService
    ) {}

    #[OA\Get(
        path: "/restaurants/{restaurantId}/tables",
        tags: ["Tables"],
        summary: "Liste les tables d'un restaurant",
        parameters: [
            new OA\Parameter(name: "restaurantId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "page", in: "query", required: false,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Tables récupérées"),
            new OA\Response(response: 404, description: "Restaurant introuvable"),
        ]
    )]
    public function index(Restaurant $restaurant)
    {
        $tables = $restaurant->activeTables()->paginate(15);
        return $this->successResponse($tables, 'Tables récupérées');
    }

    #[OA\Get(
        path: "/restaurants/{restaurantId}/tables/available",
        tags: ["Tables"],
        summary: "Tables disponibles selon date, heure et capacité",
        description: "Retourne les tables disponibles pour un créneau donné",
        parameters: [
            new OA\Parameter(name: "restaurantId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "date", in: "query", required: true,
                schema: new OA\Schema(type: "string", format: "date", example: "2026-04-01")),
            new OA\Parameter(name: "start_time", in: "query", required: true,
                schema: new OA\Schema(type: "string", example: "19:30")),
            new OA\Parameter(name: "guests", in: "query", required: true,
                schema: new OA\Schema(type: "integer", example: 4)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Tables disponibles retournées"),
            new OA\Response(response: 422, description: "Hors des horaires du restaurant"),
        ]
    )]
    public function available(Restaurant $restaurant)
    {
        $validated = request()->validate([
            'date'       => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'guests'     => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['start_time'] < $restaurant->opening_time ||
            $validated['start_time'] >= $restaurant->closing_time) {
            return $this->errorResponse(
                "Ce restaurant est ouvert de {$restaurant->opening_time} à {$restaurant->closing_time}",
                422
            );
        }

        $tables = $this->availabilityService->getAvailableTables(
            $restaurant->id,
            $validated['date'],
            $validated['start_time'],
            $validated['guests']
        );

        return $this->successResponse(
            $tables,
            $tables->isEmpty()
                ? 'Aucune table disponible pour ce créneau'
                : "{$tables->count()} table(s) disponible(s)"
        );
    }

    #[OA\Post(
        path: "/restaurants/{restaurantId}/tables",
        tags: ["Tables"],
        summary: "Créer une table (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "restaurantId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "capacity", "location"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Table VIP 1"),
                    new OA\Property(property: "capacity", type: "integer", example: 4),
                    new OA\Property(
                        property: "location",
                        type: "string",
                        enum: ["intérieur", "terrasse", "bar", "privé"],
                        example: "terrasse"
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Table créée"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 422, description: "Données invalides"),
        ]
    )]
    public function store(StoreTableRequest $request, Restaurant $restaurant)
    {
        $table = $restaurant->tables()->create($request->validated());
        return $this->createdResponse($table, 'Table créée avec succès');
    }

    #[OA\Put(
        path: "/restaurants/{restaurantId}/tables/{tableId}",
        tags: ["Tables"],
        summary: "Modifier une table (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "restaurantId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "tableId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Table A"),
                    new OA\Property(property: "capacity", type: "integer", example: 6),
                    new OA\Property(property: "is_active", type: "boolean", example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Table mise à jour"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function update(UpdateTableRequest $request, Restaurant $restaurant, Table $table)
    {
        if ($table->restaurant_id !== $restaurant->id) {
            return $this->errorResponse('Cette table n\'appartient pas à ce restaurant', 403);
        }
        $table->update($request->validated());
        return $this->successResponse($table->fresh(), 'Table mise à jour');
    }

    #[OA\Delete(
        path: "/restaurants/{restaurantId}/tables/{tableId}",
        tags: ["Tables"],
        summary: "Archiver une table (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "restaurantId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "tableId", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Table archivée"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function destroy(Restaurant $restaurant, Table $table)
    {
        if (!Auth::user()->isAdmin()) {
            return $this->forbiddenResponse();
        }
        if ($table->restaurant_id !== $restaurant->id) {
            return $this->errorResponse('Cette table n\'appartient pas à ce restaurant', 403);
        }
        $table->delete();
        return $this->successResponse(null, 'Table archivée');
    }
}