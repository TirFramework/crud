<?php

namespace Tir\Crud\Support\Helpers;

class CrudHelper {

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


    public static function version()
    {
        /**
         * Version a relative asset using the time its contents last changed.
         *
         * @param string $value
         * @return string
         */
        function v($path)
        {
            if (config('app.env') === 'local') {
                $version = uniqid();
            } else {
                $version = FleetCart::VERSION;
            }

            return "{$path}?v=" . $version;
        }
    }
}