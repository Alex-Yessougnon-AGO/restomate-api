<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\Admin\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Restomate API v1
|--------------------------------------------------------------------------
|
| Toutes les routes sont préfixées par /api automatiquement par Laravel
| On ajoute /v1 pour versionner notre API
| 
| Versioning = bonne pratique professionnelle
| Si un jour tu changes l'API, tu crées /v2 sans casser /v1
|
*/

Route::prefix('v1')->group(function () {

    // ═══════════════════════════════════════════════
    // 🔓 ROUTES PUBLIQUES — Pas besoin d'être connecté
    // ═══════════════════════════════════════════════

    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
    });

    // Consultation publique des restaurants
    Route::get('restaurants',        [RestaurantController::class, 'index']);
    Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']);

    // Consultation publique des tables d'un restaurant
    Route::get(
        'restaurants/{restaurant}/tables',
        [TableController::class, 'index']
    );

    // Vérifier les tables disponibles
    Route::get(
        'restaurants/{restaurant}/tables/available',
        [TableController::class, 'available']
    );

    // ═══════════════════════════════════════════════
    // 🔐 ROUTES PROTÉGÉES — Connexion obligatoire
    // ═══════════════════════════════════════════════
    // middleware('auth:sanctum') = Laravel vérifie que
    // la requête contient un token Sanctum valide
    // Sinon → 401 Unauthorized automatique

    Route::middleware('auth:sanctum')->group(function () {

        // ───────────────────────────────
        // Auth — Profil et déconnexion
        // ───────────────────────────────
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me',      [AuthController::class, 'me']);
        });

        // ───────────────────────────────
        // Restaurants — Actions Admin
        // ───────────────────────────────
        Route::post(
            'restaurants',
            [RestaurantController::class, 'store']
        );
        Route::put(
            'restaurants/{restaurant}',
            [RestaurantController::class, 'update']
        );
        Route::delete(
            'restaurants/{restaurant}',
            [RestaurantController::class, 'destroy']
        );

        // ───────────────────────────────
        // Tables — Actions Admin
        // ───────────────────────────────
        Route::post(
            'restaurants/{restaurant}/tables',
            [TableController::class, 'store']
        );
        Route::put(
            'restaurants/{restaurant}/tables/{table}',
            [TableController::class, 'update']
        );
        Route::delete(
            'restaurants/{restaurant}/tables/{table}',
            [TableController::class, 'destroy']
        );

        // ───────────────────────────────
        // Réservations
        // ───────────────────────────────
        Route::prefix('reservations')->group(function () {

            // Admin : toutes les réservations
            Route::get('/',   [ReservationController::class, 'index']);

            // Client : ses propres réservations
            Route::get('my',  [ReservationController::class, 'myReservations']);

            // Créer une réservation
            Route::post('/',  [ReservationController::class, 'store']);

            // Détail, modification
            Route::get('{reservation}',    [ReservationController::class, 'show']);
            Route::put('{reservation}',    [ReservationController::class, 'update']);

            // Actions sur le statut
            Route::patch(
                '{reservation}/cancel',
                [ReservationController::class, 'cancel']
            );
            Route::patch(
                '{reservation}/confirm',
                [ReservationController::class, 'confirm']
            );
            Route::patch(
                '{reservation}/no-show',
                [ReservationController::class, 'noShow']
            );
        });

        // ───────────────────────────────
        // Admin — Gestion des utilisateurs
        // ───────────────────────────────
        Route::prefix('admin')->group(function () {
            Route::get('users',                  [AdminController::class, 'users']);
            Route::get('users/{user}',           [AdminController::class, 'showUser']);
            Route::patch('users/{user}/toggle',  [AdminController::class, 'toggleUser']);
            Route::get('stats',                  [AdminController::class, 'stats']);
        });
    });
});