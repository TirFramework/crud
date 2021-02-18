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


    /**
     * @param $inputs
     * @return $this
     */
    public function add($inputs)
    {
        foreach ($inputs as $input){
            array_push($this->fields, $input->get());
        }
        return $this;

    }

}