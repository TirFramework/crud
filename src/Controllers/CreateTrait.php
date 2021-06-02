<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

//use Tir\Crud\Events\CreateEvent;

trait CreateTrait
{


    public function create()
    {
        return $this->createCrud();
    }


    public function createCrud()
    {
        return View::first([$this->crud->name.'::admin.create', "crud::scaffold.create"])->with('crud', $this->crud);
    }



}
