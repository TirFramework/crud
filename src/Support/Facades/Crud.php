<?php

namespace Tir\Crud\Support\Facades;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Tir\Crud\Support\Language\RTLDetector;

class Crud
{

    /**
     * Get current locale.
     *
     * @return string
     */
     public static function locale()
     {
         return app()->getLocale();
     }

        /**
         * Get all supported locales.
         *
         * @return array
         */
        public static function supported_locales()
        {
            return LaravelLocalization::getSupportedLocales();
        }



    /**
     * Determine if the given / current locale is RTL script.
     *
     * @param string|null $locale
     * @return bool
     */
    public static function is_rtl($locale = null)
    {
        return RTLDetector::detect($locale ?: self::locale());
    }


     

    /**
     * Reset numeric index of an array recursively.
     *
     * @param array $array
     * @return array|\Illuminate\Support\Collection
     *
     * @see https://stackoverflow.com/a/12399408/5736257
     */

    public static function array_reset_index($array)
    {

            $array = $array instanceof Collection
                ? $array->toArray()
                : $array;

            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $array[$key] = static::array_reset_index($val);
                }
            }

            if (isset($key) && is_numeric($key)) {
                return array_values($array);
            }

            return $array;
        
    }


    public static function version($path)
    {
        /**
         * Version a relative asset using the time its contents last changed.
         *
         * @param string $value
         * @return string
         */

            if (config('app.env') === 'local') {
                $version = uniqid();
            } else {
                $version = FleetCart::VERSION;
            }

            return "{$path}?v=" . $version;

    }

    public static function localized_url($locale, $url = null)
    {
        return LaravelLocalization::getLocalizedURL($locale, $url);
    }
}