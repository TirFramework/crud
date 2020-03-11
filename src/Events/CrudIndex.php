<?php

namespace Amaj\Crud\Events;

class CrudIndex
{
    //get crud name from Crud. it's well be similar to "Post" or "User" or any name of modules or packages
    public $CrudName;

    public function __construct(string $CrudName)
    {
        $this->CrudName = $CrudName;
    }
}


