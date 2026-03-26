<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table users avec tous les champs nécessaires
     * pour Restomate (auth + rôles + profil basique)
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Informations de base
            $table->string('name');
            $table->string('email', 191)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Champs métier Restomate
            $table->enum('role', ['client', 'admin'])->default('client');
            // ^ enum = liste de valeurs autorisées
            // Si quelqu'un envoie "superadmin" → MySQL refuse automatiquement

            $table->string('phone', 20)->nullable();
            // ^ nullable = ce champ n'est pas obligatoire

            $table->boolean('is_active')->default(true);
            // ^ Permet à l'admin de désactiver un compte sans le supprimer

            $table->rememberToken();
            $table->timestamps();
            // ^ timestamps() crée automatiquement created_at et updated_at
            // Laravel les met à jour automatiquement à chaque création/modification
        });

        // Tables système Laravel - ne pas toucher
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Supprime les tables si on annule cette migration
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};