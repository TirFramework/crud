<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
use Tir\Crud\Events\CreateEvent;

trait CreateTrait
{

    /**
    * This function called from route. run an event and run createCrud functions
    * @return void
    */
    public function create()
    {
        event(new CreateEvent($this->name));
        return $this->createCrud();
    }


     /**
     * This function return a view and pass $crud
     * @return \Illuminate\Support\Facades\View;
     */
    public function createCrud()
    {
        /*
         * First try to load a view from application or other package, that called
         * this function with CRUD name. if this view wasn't exist then try
         * load create view from CRUD(this) package.
         */
        return View::first(["$this->name::admin.create", "crud::scaffold.create"])->with('crud', $this->crud);
    }



}
