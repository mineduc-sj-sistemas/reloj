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
        Model::shouldBeStrict(!app()->isProduction());

        // Límite para API del Dashboard (120 req/min por IP)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Límite para simulación de fichadas de prueba (20 req/min por IP)
        RateLimiter::for('simulate', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // Límite para comunicación de dispositivos ZKTeco (300 req/min por IP)
        RateLimiter::for('adms', function (Request $request) {
            return Limit::perMinute(300)->by($request->ip());
        });
    }
}
