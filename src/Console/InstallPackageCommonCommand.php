<?php

namespace Ecs\Common\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class InstallPackageCommonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecs:installation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installation ECS package common';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->publishProviders();
        $this->publishHelpers();
        $this->publishRepositories();

        $this->info("Installation complete!");
    }

    private function publishProviders()
    {
        Artisan::call('vendor:publish --tag="ecs-provider"');

        $providerFiles = File::allFiles(base_path("vendor/gmo-ecs/ecs-common-source/src/Providers"));

        foreach ($providerFiles as $file) {
            $relativePath = "App\\Providers\\" . $file->getFilenameWithoutExtension();
            ServiceProvider::addProviderToBootstrapFile(
                $relativePath,
                app()->getBootstrapProvidersPath(),
            );
        }
    }

    private function publishHelpers()
    {
        Artisan::call('vendor:publish --tag="ecs-helper"');

        $composerFilePath = base_path('composer.json');
        $composerJson = json_decode(file_get_contents($composerFilePath), true);
        $helperPath = "app/Helpers/Helper.php";

        if (!in_array($helperPath, data_get($composerJson['autoload'], "files", []))) {
            $composerJson['autoload']['files'][] = $helperPath;

            file_put_contents($composerFilePath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        exec('composer dump-autoload');
    }

    private function publishRepositories()
    {
        Artisan::call('vendor:publish --tag="ecs-repository"');
    }
}
