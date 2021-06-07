<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

trait IndexTrait
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return View::first([$this->model->getModuleName() . "::admin.index", "core::scaffold.index"], ['model' => $this->model]);
    }
}
