<?php

namespace Ecs\Command;

use Ecs\Command\Console\RepositoryMakeCommand;
use Ecs\Command\Console\ServiceMakeCommand;
use Ecs\Command\Console\CustomControllerMakeCommand;
use Illuminate\Support\ServiceProvider;

class EcsCommandServiceProvider extends ServiceProvider
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
            $this->publishes([
                __DIR__ . '/../Repositories/BaseRepository.php' => app_path('Repositories/BaseRepository.php')
            ], 'base-repository');

            $this->commands([
                CustomControllerMakeCommand::class,
                RepositoryMakeCommand::class,
                ServiceMakeCommand::class,
            ]);
        }
    }
}
