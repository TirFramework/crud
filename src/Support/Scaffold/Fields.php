<?php


namespace Tir\Crud\Support\Scaffold;


use Tir\Crud\Support\Scaffold\Inputs\Text;

class Fields
{
    private array $fields = [];

    /**
     * @return array
     */
    public function get()
    {
        return $this->fields;
    }


    public function text(Text $text)
    {
        return $text;

    }

}