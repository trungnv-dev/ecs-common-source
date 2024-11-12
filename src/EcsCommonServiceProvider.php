<?php

namespace Ecs\Common;

use Ecs\Common\Console\CustomControllerMakeCommand;
use Ecs\Common\Console\RepositoryMakeCommand;
use Ecs\Common\Console\ServiceMakeCommand;
use Illuminate\Support\ServiceProvider;

class EcsCommonServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO..
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publishes base common
            $this->publishes([
                __DIR__ . '/Repositories/BaseRepository.php' => app_path('Repositories/BaseRepository.php'),
                __DIR__ . '/Providers/DatabaseServiceProvider.php' => app_path('Providers/DatabaseServiceProvider.php'),
                __DIR__ . '/Providers/ModelServiceProvider.php' => app_path('Providers/ModelServiceProvider.php'),
                __DIR__ . '/Providers/RouteServiceProvider.php' => app_path('Providers/RouteServiceProvider.php'),
                __DIR__ . '/Providers/URLServiceProvider.php' => app_path('Providers/URLServiceProvider.php'),
                __DIR__ . '/Providers/ViewServiceProvider.php' => app_path('Providers/ViewServiceProvider.php'),
                __DIR__ . '/Providers/ViteServiceProvider.php' => app_path('Providers/ViteServiceProvider.php'),
            ], 'base-common');

            // 
            // $this->mergeConfigFrom('')

            // Boot commands
            $this->commands([
                CustomControllerMakeCommand::class,
                RepositoryMakeCommand::class,
                ServiceMakeCommand::class,
            ]);
        }
    }
}
