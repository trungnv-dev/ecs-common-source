<?php

namespace Ecs\RepositoryCommand\Providers;

use Ecs\RepositoryCommand\Console\RepositoryMakeCommand;
use Ecs\RepositoryCommand\Console\ServiceMakeCommand;
use Illuminate\Support\ServiceProvider;

class RepositoryCommandServiceProvider extends ServiceProvider
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
                RepositoryMakeCommand::class,
                ServiceMakeCommand::class,
            ]);
        }
    }
}
