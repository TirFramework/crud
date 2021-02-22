<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
use Tir\Crud\Support\Scaffold\Crud;

trait IndexTrait
{
    /**
     *  This function return and pass crud value to the index view.
     * @return View index
     */
    public function index()
    {
        return  Crud::getFields();
        return View::first([Crud::name()."::admin.index", "crud::scaffold.index"])->with('crud', $this->m);
    }

}
