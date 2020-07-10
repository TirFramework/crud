<?php

namespace Tir\Crud\Events;

class GetCrudEvent
{
    public $crud;

    public function __construct($crud)
    {
        $this->crud = $crud;
    }
}


