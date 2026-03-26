<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Table;
use App\Services\AvailabilityService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class ReservationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AvailabilityService $availabilityService
    ) {}

    #[OA\Get(
        path: "/reservations",
        tags: ["Réservations"],
        summary: "Toutes les réservations (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "status", in: "query", required: false,
                schema: new OA\Schema(
                    type: "string",
                    enum: ["pending", "confirmed", "cancelled", "no_show"]
                )),
            new OA\Parameter(name: "date", in: "query", required: false,
                schema: new OA\Schema(type: "string", format: "date", example: "2026-04-01")),
            new OA\Parameter(name: "restaurant_id", in: "query", required: false,
                schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "page", in: "query", required: false,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Réservations récupérées"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            return $this->forbiddenResponse();
        }
        $reservations = Reservation::query()
            ->with(['client', 'table.restaurant'])
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->when(request('date'), fn($q, $d) => $q->where('reservation_date', $d))
            ->when(request('restaurant_id'), function ($q, $rid) {
                $q->whereHas('table', fn($t) => $t->where('restaurant_id', $rid));
            })
            ->latest()
            ->paginate(15);
        return $this->successResponse($reservations, 'Réservations récupérées');
    }

    #[OA\Get(
        path: "/reservations/my",
        tags: ["Réservations"],
        summary: "Mes réservations (Client connecté)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "status", in: "query", required: false,
                schema: new OA\Schema(type: "string",
                    enum: ["pending", "confirmed", "cancelled", "no_show"])),
        ],
        responses: [
            new OA\Response(response: 200, description: "Réservations récupérées"),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function myReservations()
    {
        $reservations = Reservation::where('client_id', Auth::id())
            ->with(['table.restaurant'])
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10);
        return $this->successResponse($reservations, 'Vos réservations récupérées');
    }

    #[OA\Post(
        path: "/reservations",
        tags: ["Réservations"],
        summary: "Créer une réservation (Client)",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["table_id", "reservation_date", "start_time", "number_of_guests"],
                properties: [
                    new OA\Property(property: "table_id", type: "integer", example: 1),
                    new OA\Property(property: "reservation_date", type: "string",
                        format: "date", example: "2026-04-01"),
                    new OA\Property(property: "start_time", type: "string", example: "19:30"),
                    new OA\Property(property: "number_of_guests", type: "integer", example: 3),
                    new OA\Property(property: "special_requests", type: "string",
                        example: "Allergie aux noix, anniversaire"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Réservation créée"),
            new OA\Response(response: 409, description: "Table non disponible"),
            new OA\Response(response: 422, description: "Données invalides"),
        ]
    )]
    public function store(StoreReservationRequest $request)
    {
        $table = Table::findOrFail($request->table_id);

        if ($request->number_of_guests > $table->capacity) {
            return $this->errorResponse(
                "Cette table accueille maximum {$table->capacity} personnes.", 422);
        }

        $isAvailable = $this->availabilityService->isTableAvailable(
            $table->id, $request->reservation_date, $request->start_time
        );

        if (!$isAvailable) {
            return $this->errorResponse(
                'Cette table n\'est pas disponible pour ce créneau.', 409);
        }

        $endTime = $this->availabilityService->calculateEndTime($request->start_time);

        $reservation = Reservation::create([
            'client_id'        => Auth::id(),
            'table_id'         => $request->table_id,
            'reservation_date' => $request->reservation_date,
            'start_time'       => $request->start_time,
            'end_time'         => $endTime,
            'number_of_guests' => $request->number_of_guests,
            'special_requests' => $request->special_requests,
            'status'           => 'pending',
        ]);

        return $this->createdResponse(
            $reservation->load(['client', 'table.restaurant']),
            'Réservation créée avec succès'
        );
    }

    #[OA\Get(
        path: "/reservations/{id}",
        tags: ["Réservations"],
        summary: "Détail d'une réservation",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Réservation récupérée"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 404, description: "Introuvable"),
        ]
    )]
    public function show(Reservation $reservation)
    {
        if (!Auth::user()->isAdmin() &&
            !$reservation->belongsToClient(Auth::id())) {
            return $this->forbiddenResponse('Vous n\'avez pas accès à cette réservation');
        }
        return $this->successResponse(
            $reservation->load(['client', 'table.restaurant']),
            'Réservation récupérée'
        );
    }

    #[OA\Put(
        path: "/reservations/{id}",
        tags: ["Réservations"],
        summary: "Modifier une réservation",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "reservation_date", type: "string",
                        format: "date", example: "2026-04-02"),
                    new OA\Property(property: "start_time", type: "string", example: "20:00"),
                    new OA\Property(property: "number_of_guests", type: "integer", example: 2),
                    new OA\Property(property: "special_requests", type: "string",
                        example: "Table près de la fenêtre"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Réservation mise à jour"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 422, description: "Impossible de modifier"),
        ]
    )]
    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        if (!Auth::user()->isAdmin() && !$reservation->belongsToClient(Auth::id())) {
            return $this->forbiddenResponse();
        }
        if ($reservation->status === 'confirmed' && !Auth::user()->isAdmin()) {
            return $this->errorResponse('Impossible de modifier une réservation confirmée.', 422);
        }
        $reservation->update($request->validated());
        return $this->successResponse(
            $reservation->fresh()->load(['client', 'table.restaurant']),
            'Réservation mise à jour'
        );
    }

    #[OA\Patch(
        path: "/reservations/{id}/cancel",
        tags: ["Réservations"],
        summary: "Annuler une réservation",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Réservation annulée"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 422, description: "Annulation impossible"),
        ]
    )]
    public function cancel(Reservation $reservation)
    {
        if (!Auth::user()->isAdmin() && !$reservation->belongsToClient(Auth::id())) {
            return $this->forbiddenResponse();
        }
        if (!$reservation->canBeCancelled()) {
            return $this->errorResponse('Cette réservation ne peut plus être annulée.', 422);
        }
        $reservation->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        return $this->successResponse($reservation->fresh(), 'Réservation annulée');
    }

    #[OA\Patch(
        path: "/reservations/{id}/confirm",
        tags: ["Réservations"],
        summary: "Confirmer une réservation (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Réservation confirmée"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 422, description: "Statut incorrect"),
        ]
    )]
    public function confirm(Reservation $reservation)
    {
        if (!Auth::user()->isAdmin()) {
            return $this->forbiddenResponse();
        }
        if ($reservation->status !== 'pending') {
            return $this->errorResponse(
                'Seules les réservations en attente peuvent être confirmées.', 422);
        }
        $reservation->update(['status' => 'confirmed']);
        return $this->successResponse(
            $reservation->fresh()->load(['client', 'table.restaurant']),
            'Réservation confirmée'
        );
    }

    #[OA\Patch(
        path: "/reservations/{id}/no-show",
        tags: ["Réservations"],
        summary: "Marquer no-show (Admin)",
        description: "Marque une réservation comme no-show quand le client ne s'est pas présenté",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Marqué no-show"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 422, description: "Statut incorrect"),
        ]
    )]
    public function noShow(Reservation $reservation)
    {
        if (!Auth::user()->isAdmin()) {
            return $this->forbiddenResponse();
        }
        if ($reservation->status !== 'confirmed') {
            return $this->errorResponse(
                'Seules les réservations confirmées peuvent être marquées no-show.', 422);
        }
        $reservation->update(['status' => 'no_show']);
        return $this->successResponse($reservation->fresh(), 'Réservation marquée no-show');
    }
}