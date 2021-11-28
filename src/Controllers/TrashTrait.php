<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
use Tir\Crud\Events\TrashEvent;

trait TrashTrait
{
    /**
     *  This function return and pass crud value to the index view.
     * @return \Illuminate\Support\Facades\View index
     */
    public function trash()
    {
        //here we can add some functionality with other packages or in application
        event(new TrashEvent($this->name));
        return View::first(["$this->name::admin.index", "core::scaffold.index"])->with(['crud' => $this->crud, 'trash' => true]);
        
    }

}
