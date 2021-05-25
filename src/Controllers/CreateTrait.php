<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
//use Tir\Crud\Events\CreateEvent;

trait CreateTrait
{


    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        //event(new CreateEvent($this->name));
        return $this->createCrud();
    }


    /**
     * First try to load a view from application or other package, that called
     * this function with CRUD name. if this view wasn't exist then try
     * load create view from CRUD(this) package.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function createCrud()
    {
        return View::first([$this->crud->name.'::admin.create', "crud::scaffold.create"])->with('crud', $this->crud);
    }



}
