<?php

namespace Tir\Crud\Support\Scaffold;


use Tir\Crud\Support\Scaffold\Inputs\Text;

class Fields
{
    private array $fields = [];

    /**
     * @return array
     */
    public function get():array
    {
        return json_decode(json_encode($this->fields), false);
    }


    public function add($inputs=[])
    {
        foreach ($inputs as $input){
            array_push($this->fields, $input->get());
        }

    }

}