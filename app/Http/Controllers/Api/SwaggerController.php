<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: "1.0.0",
        title: "Restomate API",
        description: "API de réservation de tables de restaurant. Gérez restaurants, tables et réservations avec authentification Sanctum.",
        contact: new OA\Contact(
            name: "Alex AGO",
            email: "alexyessougnonago@gmail.com"
        )
    ),
    OA\Server(
        url: "http://restomate-api.test/api/v1",
        description: "Serveur de développement"
    ),
    OA\SecurityScheme(
        securityScheme: "bearerAuth",
        type: "http",
        scheme: "bearer",
        bearerFormat: "JWT",
        description: "Entre ton token Sanctum obtenu après login"
    ),
    OA\Tag(name: "Authentification", description: "Inscription, connexion, profil"),
    OA\Tag(name: "Restaurants", description: "Gestion des restaurants"),
    OA\Tag(name: "Tables", description: "Gestion des tables"),
    OA\Tag(name: "Réservations", description: "Gestion des réservations"),
    OA\Tag(name: "Administration", description: "Gestion admin des utilisateurs")
]
class SwaggerController {}