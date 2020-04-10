<?php

namespace Tir\Crud\Controllers;

use Tir\Crud\Events\IndexEvent;
use Illuminate\Support\Facades\View;

trait IndexTrait
{
    /**
     *  This function return and pass crud value to the index view.
     * @return \Illuminate\Support\Facades\View index
     */
    public function index()
    {
        //here we can add some functionality with other packages or in application
        //event(new IndexEvent($this->name));
        return View::first(["$this->name::admin.index", "crud::scaffold.index"])->with('crud', $this->crud);
    }

}
