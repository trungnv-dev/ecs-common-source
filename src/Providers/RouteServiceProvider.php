<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        $this->configureUsingRoute();
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure the using routing for the application.
     *
     * @return void
     */
    protected function configureUsingRoute()
    {
        $this->routes(function () {
            if (file_exists($routeApi = base_path('routes/api.php'))) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($routeApi);
            }

            if (file_exists($routeAdmin = base_path('routes/admin.php'))) {
                Route::middleware('web')
                    ->prefix('admin')
                    ->name('admin.')
                    ->group($routeAdmin);
            }

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
