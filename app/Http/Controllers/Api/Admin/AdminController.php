<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Reservation;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    use ApiResponse;

    private function checkAdmin(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    #[OA\Get(
        path: "/admin/users",
        tags: ["Administration"],
        summary: "Liste tous les clients (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "search", in: "query", required: false,
                schema: new OA\Schema(type: "string", example: "Alex")),
            new OA\Parameter(name: "page", in: "query", required: false,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Utilisateurs récupérés"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function users()
    {
        if (!$this->checkAdmin()) return $this->forbiddenResponse();

        $users = User::where('role', 'client')
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->withCount('reservations')
            ->paginate(15);

        return $this->successResponse($users, 'Utilisateurs récupérés');
    }

    #[OA\Get(
        path: "/admin/users/{id}",
        tags: ["Administration"],
        summary: "Détail d'un client avec historique (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Utilisateur récupéré"),
            new OA\Response(response: 403, description: "Accès refusé"),
            new OA\Response(response: 404, description: "Introuvable"),
        ]
    )]
    public function showUser(User $user)
    {
        if (!$this->checkAdmin()) return $this->forbiddenResponse();

        $user->load(['reservations.table.restaurant']);
        $user->loadCount('reservations');

        return $this->successResponse($user, 'Utilisateur récupéré');
    }

    #[OA\Patch(
        path: "/admin/users/{id}/toggle",
        tags: ["Administration"],
        summary: "Activer ou désactiver un compte client (Admin)",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true,
                schema: new OA\Schema(type: "integer", example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Compte activé ou désactivé"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function toggleUser(User $user)
    {
        if (!$this->checkAdmin()) return $this->forbiddenResponse();

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activé' : 'désactivé';

        return $this->successResponse($user->fresh(), "Compte {$status} avec succès");
    }

    #[OA\Get(
        path: "/admin/stats",
        tags: ["Administration"],
        summary: "Statistiques globales de la plateforme (Admin)",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Statistiques récupérées"),
            new OA\Response(response: 403, description: "Accès refusé"),
        ]
    )]
    public function stats()
    {
        if (!$this->checkAdmin()) return $this->forbiddenResponse();

        return $this->successResponse([
            'total_restaurants'  => Restaurant::count(),
            'total_clients'      => User::where('role', 'client')->count(),
            'total_reservations' => Reservation::count(),
            'reservations_today' => Reservation::whereDate('reservation_date', today())->count(),
            'by_status' => [
                'pending'   => Reservation::where('status', 'pending')->count(),
                'confirmed' => Reservation::where('status', 'confirmed')->count(),
                'cancelled' => Reservation::where('status', 'cancelled')->count(),
                'no_show'   => Reservation::where('status', 'no_show')->count(),
            ],
        ], 'Statistiques récupérées');
    }
}