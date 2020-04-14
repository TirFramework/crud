<?php


namespace Tir\Crud\Support\Html;


class Form
{
    /**
     * @param string $name name
     * @param string $value value
     * @param array $options options
     * @return string
     */
    public static function Text($name, $value, $options=[])
    {
        return '<div class="{{$field->col ?? \'col-12 col-md-6\'}}"> \n'.

                '</div>';

    }
}