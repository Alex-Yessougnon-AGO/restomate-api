<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Fix pour MySQL : limite la longueur des index à 191 caractères
        // Résout l'erreur "Specified key was too long"
        Schema::defaultStringLength(191);
    }
}
