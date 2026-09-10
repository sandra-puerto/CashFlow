<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Inicializa la configuración global de la aplicación.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->configureRateLimiting();
    }

    /**
     * Habilita el modo estricto de Eloquent fuera de producción para
     * detectar consultas N+1 y accesos perezosos a atributos no cargados.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
    }

    /**
     * Registra el limitador de frecuencia para el grupo de rutas "api".
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}