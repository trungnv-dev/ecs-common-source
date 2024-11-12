<?php

namespace Ecs\Common;

use Ecs\Common\Console\CustomControllerMakeCommand;
use Ecs\Common\Console\InstallPackageCommonCommand;
use Ecs\Common\Console\RepositoryMakeCommand;
use Ecs\Common\Console\ServiceMakeCommand;
use Illuminate\Support\Facades\File;
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
        // 
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootPublishProviders();
        $this->bootPublishHelpers();
        $this->bootPublishRepositories();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CustomControllerMakeCommand::class,
                InstallPackageCommonCommand::class,
                RepositoryMakeCommand::class,
                ServiceMakeCommand::class,
            ]);
        }
    }

    private function bootPublishProviders()
    {
        $providerFiles = File::allFiles(__DIR__ . '/Providers');
        $publishProviderPaths = [];

        foreach ($providerFiles as $file) {
            $fileName = $file->getFilename();
            $publishProviderPaths += [
                __DIR__ . "/Providers/$fileName" => app_path("Providers/$fileName")
            ];
        }

        $this->publishes($publishProviderPaths, 'ecs-provider');
    }

    private function bootPublishHelpers()
    {
        $this->publishes([
            __DIR__ . '/Helpers/Helper.php' => app_path('Helpers/Helper.php'),
        ], 'ecs-helper');
    }

    private function bootPublishRepositories()
    {
        $this->publishes([
            __DIR__ . '/Repositories/BaseRepository.php' => app_path('Repositories/BaseRepository.php'),
        ], 'ecs-repository');
    }
}
