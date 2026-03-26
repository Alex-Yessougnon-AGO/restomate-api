<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: "/auth/register",
        tags: ["Authentification"],
        summary: "Inscrire un nouveau client",
        description: "Crée un compte client et retourne un token d'authentification",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Alex AGO"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "alex@example.com"),
                    new OA\Property(property: "password", type: "string", minLength: 8, example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "password123"),
                    new OA\Property(property: "phone", type: "string", example: "+22997000000"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Inscription réussie"),
            new OA\Response(response: 422, description: "Données invalides"),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'phone'    => $request->phone,
            'role'     => 'client',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->createdResponse([
            'user'  => $user,
            'token' => $token,
        ], 'Inscription réussie');
    }

    #[OA\Post(
        path: "/auth/login",
        tags: ["Authentification"],
        summary: "Connecter un utilisateur",
        description: "Retourne un token d'authentification valide",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "alex@example.com"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Connexion réussie"),
            new OA\Response(response: 401, description: "Identifiants incorrects"),
            new OA\Response(response: 403, description: "Compte désactivé"),
        ]
    )]
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse('Email ou mot de passe incorrect', 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user->is_active) {
            return $this->errorResponse('Votre compte a été désactivé.', 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user'  => $user,
            'token' => $token,
        ], 'Connexion réussie');
    }

    #[OA\Post(
        path: "/auth/logout",
        tags: ["Authentification"],
        summary: "Déconnecter l'utilisateur",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Déconnexion réussie"),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function logout()
    {
        Auth::user()->tokens()->where(
            'id',
            Auth::user()->currentAccessToken()->id
        )->delete();

        return $this->successResponse(null, 'Déconnexion réussie');
    }

    #[OA\Get(
        path: "/auth/me",
        tags: ["Authentification"],
        summary: "Profil de l'utilisateur connecté",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Profil récupéré"),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function me()
    {
        return $this->successResponse(Auth::user(), 'Profil récupéré');
    }
}