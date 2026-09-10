<?php

namespace App\Providers;

<<<<<<< HEAD
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
=======
>>>>>>> fbf79995eb7e323766be37cda723484187a892a2
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
<<<<<<< HEAD
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
=======
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
    }
}
>>>>>>> fbf79995eb7e323766be37cda723484187a892a2
