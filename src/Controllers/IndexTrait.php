<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

trait IndexTrait
{
    public function index()
    {
        $this->executeAccess('index');
        return View::first([$this->crud->name . "::admin.index", "crud::scaffold.index"], ['crud' => $this->crud]);
    }
}
