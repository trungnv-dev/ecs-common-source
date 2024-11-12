<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    /**
     * Define your file storage.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureMarco();
    }

    /**
     * Configure the marco file service for the application.
     */
    protected function configureMarco()
    {
        /**
         * Register marco upload one or more files.
         *
         * @param string $folder
         * @param \Illuminate\Http\UploadedFile|array $files
         * @param ?string $fileName
         *
         * @return array
         */
        Storage::macro('upload', function ($folder, $files, $fileName = null) {
            $upload = function ($file) use ($folder, $fileName) {
                $fileName = $fileName ?? $file->hashName();
                $path = Storage::putFileAs($folder, $file, $fileName);

                return [
                    'path' => $path,
                    'fullUrl' => Storage::getUrl($path),
                    'fileName' => $fileName,
                ];
            };

            if (is_array($files)) {
                return array_map(fn ($file) => $upload($file), $files);
            }

            return $upload($files);
        });

        /**
         * Register marco move one or more files.
         *
         * @param array $paths
         *
         * @return bool
         */
        Storage::macro('moves', function ($paths) {
            return array_walk($paths, fn($to, $from) => Storage::move($from, $to));
        });

        /**
         * Register marco get full url based on specified path.
         *
         * @param string $path
         * @param bool $isNoImage // Get image default if file path not exists.
         *
         * @return string
         */
        Storage::macro('getUrl', function ($path, $isNoImage = false) {
            if (empty($path) || Storage::missing($path)) {
                return $isNoImage ? Vite::image('front/nowprinting.jpg') : '';
            }

            return config('filesystems.default') != 's3'
                ? Storage::url($path)
                : (
                    config('filesystems.disks.s3.url')
                        ? Storage::url($path) // Get file from CloudFront origin
                        : Storage::temporaryUrl($path, now()->addMinutes(15)) // Get file temporary from S3 origin
                );
        });
    }
}
