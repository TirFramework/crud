<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

trait CreateTrait
{

    public function create()
    {
        return View::first([$this->model->moduleName . '::admin.create', "core::scaffold.create"])->with('model', $this->model);
    }



}
