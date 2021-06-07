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
        return View::first([$this->model->moduleName . '::admin.create', "core::scaffold.create"])->with('model', $this->model);
    }



}
