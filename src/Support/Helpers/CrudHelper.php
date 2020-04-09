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
}