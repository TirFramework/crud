<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;

trait IndexTrait
{
    /**
     *  This function return and pass crud value to the index view.
     */
    public function index()
    {
        return View::first([$this->crud->name."::admin.index", "crud::scaffold.index"],['crud'=>$this->crud]);
    }

}
