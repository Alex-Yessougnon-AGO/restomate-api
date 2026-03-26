<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Réponse de succès standard
     */
    protected function successResponse(
        $data,
        string $message = 'Opération réussie',
        int $statusCode = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Réponse de création (201 Created)
     */
    protected function createdResponse(
        $data,
        string $message = 'Créé avec succès'
    ) {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Réponse d'erreur standard
     */
    protected function errorResponse(
        string $message,
        int $statusCode = 400,
        $errors = null
    ) {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse 404 Not Found
     */
    protected function notFoundResponse(
        string $message = 'Ressource introuvable'
    ) {
        return $this->errorResponse($message, 404);
    }

    /**
     * Réponse 403 Forbidden
     */
    protected function forbiddenResponse(
        string $message = 'Action non autorisée'
    ) {
        return $this->errorResponse($message, 403);
    }
}