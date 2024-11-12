<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * @param $folder
     * @param $file
     * @param $convertTypeFile
     * @return string
     */
    public static function upload($folder, $file): array
    {
        $fileName = static::generateFileName($file);
        Storage::putFileAs($folder, $file, $fileName);

        return ['path' => "$folder/$fileName", "fileName" => $fileName];
    }

    /**
     * @param $folder
     * @param array $files
     * @return array
     */
    public static function uploads($folder, array $files = []): array
    {
        $filePathArr = [];
        foreach ($files as $file) {
            $filePathArr[] = static::upload($folder, $file);
        }

        return $filePathArr;
    }

    /**
     * @param $filePath
     * @param $isNoImage
     * @param $imageAi
     * @return string
     */
    public static function getUrl($filePath, $isNoImage = true, $imageAi = true): string
    {
        if (empty($filePath) || !Storage::exists($filePath)) {
            return $isNoImage
                ? ($imageAi
                    ? config('app.url') . '/build/libraries/build/assets_custom/images/common/avata-ai.png'
                    : config('app.url') . '/build/libraries/build/assets_custom/images/common/avata-none.png'
                )
                : '';
        }

        return config('filesystems.default') != 's3'
            ? Storage::url($filePath)
            : (
                config('filesystems.disks.s3.url')
                    ? Storage::url($filePath)
                    : Cache::remember($filePath, 15 * 60, function () use ($filePath) {
                        return Storage::temporaryUrl($filePath, now()->addMinutes(15));
                    })
            );
    }

    /**
     * @param $file
     * @param $convertTypeFile
     * @return string
     */
    public static function generateFileName($file, $convertTypeFile = null): string
    {
        return now()->timestamp . Str::uuid()->getHex() . '.' . $file->getClientOriginalExtension();
    }

    /**
     * @param $filePath
     */
    public static function delete($filePath)
    {
        Storage::delete($filePath);
    }

    public static function uploadImgBase64($folder, $base64)
    {
        [$extension, $image] = explode(';', $base64);

        // Extension
        $tmpExtension = explode('/', $extension);
        $extension = !empty($tmpExtension[1]) ? $tmpExtension[1] : 'jpg';

        // Image
        [, $image] = explode(',', $image);
        $image = base64_decode($image);
        $fileName = now()->timestamp . Str::uuid()->getHex() . '.' . $extension;
        Storage::put($folder . '/' . $fileName, $image);

        return ['path' => "$folder/$fileName"];
    }
}
