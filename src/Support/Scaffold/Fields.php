<?php

namespace Tir\Crud\Support\Scaffold;


use Tir\Crud\Support\Scaffold\Inputs\Text;

class Fields
{
    private static array $fields = [];

    /**
     * @return array
     */
    public static function get()
    {
        return static::$fields;
    }


    public static function add($inputs=[])
    {
        foreach ($inputs as $input){
            array_push(static::$fields, $input->get());
        }
        return static::$fields;

    }

}