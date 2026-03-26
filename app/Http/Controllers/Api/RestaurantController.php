<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\StoreRestaurantRequest;
use App\Http\Requests\Restaurant\UpdateRestaurantRequest;
use App\Models\Restaurant;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class RestaurantController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/restaurants",
        tags: ["Restaurants"],
        summary: "Liste tous les restaurants",
        description: "Retourne la liste paginée des restaurants actifs",
        parameters: [
            new OA\Parameter(name: "city", in: "query", required: false,
                schema: new OA\Schema(type: "string", example: "Cotonou")),
            new OA\Parameter(name: "search", in: "query", required: false,
                schema: new OA\Schema(type: "string", example: "Chez Mama")),
            new OA\Parameter(name: "page", in: "query", required: false,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Liste récupérée"),
        ]
    )]
    public function index()
    {
        $restaurants = Restaurant::query()
            ->when(request('city'), fn($q, $city) =>
                $q->where('city', 'like', "%{$city}%"))
            ->when(request('search'), fn($q, $search) =>
                $q->where('name', 'like', "%{$search}%"))
            ->where('is_active', true)
            ->withCount('activeTables')
            ->paginate(10);

        return $this->successResponse($restaurants, 'Restaurants récupérés');
    }

    #[OA\Post(
        path: "/restaurants",
        tags: ["Restaurants"],
        summary: "Créer un restaurant (Admin)",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "address", "city", "opening_time", "closing_time"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Chez Mama Africa"),
                    new OA\Property(property: "description", type: "string", example: "Restaurant africain"),
                    new OA\Property(property: "address", type: "string", example: "Rue des Cocotiers"),
                    new OA\Property(property: "city", type: "string", example: "Cotonou"),
                    new OA\Property(property: "phone", type: "string", example: "+22997000000"),
                    new OA\Property(property: "opening_time", type: "string", example: "08:00"),
                    new OA\Property(property: "closing_time", type: "string", example: "23:00"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Restaurant créé"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 422, description: "Données invalides"),
        ]
    )]
    public function store(StoreRestaurantRequest $request)
    {
        $restaurant = Restaurant::create($request->validated());
        return $this->createdResponse($restaurant, 'Restaurant créé avec succès');
    }

    #[OA\Get(
        path: "/restaurants/{id}",
        tags: ["Restaurants"],
        summary: "Détail d'un restaurant",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Restaurant récupéré"),
            new OA\Response(response: 404, description: "Introuvable"),
        ]
    )]
    public function show(Restaurant $restaurant)
    {
        $restaurant->load('activeTables');
        return $this->successResponse($restaurant, 'Restaurant récupéré');
    }

    #[OA\Put(
        path: "/restaurants/{id}",
        tags: ["Restaurants"],
        summary: "Modifier un restaurant (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nouveau nom"),
                    new OA\Property(property: "city", type: "string", example: "Porto-Novo"),
                    new OA\Property(property: "is_active", type: "boolean", example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Restaurant mis à jour"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant)
    {
        $restaurant->update($request->validated());
        return $this->successResponse($restaurant->fresh(), 'Restaurant mis à jour');
    }

    #[OA\Delete(
        path: "/restaurants/{id}",
        tags: ["Restaurants"],
        summary: "Archiver un restaurant (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Restaurant archivé"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return $this->successResponse(null, 'Restaurant archivé');
    }
}