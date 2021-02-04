<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

trait IndexTrait
{
    /**
     *  This function return and pass crud value to the index view.
     * @return View index
     */
    public function index()
    {
        return View::first(["$this->name::admin.index", "crud::scaffold.index"])->with('crud', $this->crud);
    }

}
