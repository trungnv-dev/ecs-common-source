<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

if (!function_exists('invalid_form')) {
    function invalid_form($name, $errors, $isCustom = false)
    {
        if (!empty($name) && $errors->has($name)) {
            if ($isCustom) {
                return 'errorCustom autofocus';
            }
            return 'formError autofocus';
        }

        return '';
    }
}

if (!function_exists('active_menu')) {
    function active_menu(array $routeName = [], ?string $option = null): string
    {
        if (in_array(Route::currentRouteName(), $routeName)) {
            return $option ?? 'current';
        }

        return '';
    }
}

if (!function_exists('img_path')) {
    function img_path($path, $isNoImage = true)
    {
        return Storage::getUrl($path, $isNoImage);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'Y/m/d')
    {
        return !empty($date) ? Carbon::parse($date)->format($format) : '';
    }
}
