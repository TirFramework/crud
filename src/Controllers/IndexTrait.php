<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
use Tir\Crud\Events\CrudIndex;
use Yajra\DataTables\Facades\DataTables;

trait IndexTrait
{
    /**
     *  This function return and pass crud value to the index view.
     * @return \Illuminate\Support\Facades\View index
     */
    public function index()
    {
        //here we can add some functionality with other packages or in application
        event(new CrudIndex($this->name));
        return View::first(["$this->name::admin.index", "crud::scaffold.index"])->with('crud', $this->crud);
    }

}
