<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

trait IndexTrait
{
    public function index()
    {
//        $this->executeAccess('index');
        $model = new $this->model;
        return View::first([$model->getScaffoldName() . "::admin.index", "crud::scaffold.index"], ['model' => $model]);
    }
}
