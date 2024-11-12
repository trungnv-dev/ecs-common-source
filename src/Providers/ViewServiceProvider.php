<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Define your view.
     */
    public function boot(): void
    {
        $this->configureViewShare();
    }

    /**
     * Configure the view share for the application.
     */
    protected function configureViewShare()
    {
        /**
         * Share translation folder special.
         *
         * @param \Illuminate\View\View $view
         *
         * @return mixed
         */
        View::composer('elements.js-translation', function ($view) {
            $view->with('translation', collect(File::files(lang_path(app()->getLocale())))
                ->flatMap(function ($file) {
                    return [
                        ($translation = $file->getBasename('.php')) => trans($translation),
                    ];
                })->toJson()
            );
        });
    }
}
