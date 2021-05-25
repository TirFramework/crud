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
        return View::first([$this->scaffold->getName()."::admin.index", "crud::scaffold.index"],['scaffold'=>$this->scaffold]);
    }

}
