<?php

namespace Tir\Crud\Controllers;

use Tir\Crud\Events\EditEvent;
use Illuminate\Support\Facades\View;

trait EditTrait
{
    /**
     * This function find an object model and if permission == owner return only owner item
     * @return eloquent
     */
    public function findForEdit($id)
    {
        $items = $this->model::findOrFail($id);
        if($this->permission == 'owner'){
            $items = $items->OnlyOwner();
        }
        return $items;
    }

    /**
     * This function return a view and pass $crud
     * @return \Illuminate\Support\Facades\View;
     */
    public function editCrud($item)
    {
        /*
         * First try to load a view from application or other package, that called
         * this function with CRUD name. if this view wasn't exist then try
         * load create view from CRUD(this) package.
         */


        // return request();
        return View::first(["$this->name::admin.edit", "crud::scaffold.edit"])->with(['crud'=>$this->crud, 'item'=>$item , 'tab' => request()->input('tab') ]);
    }

    /**
     * This function called from route. run an event and run edit functions
     *
     * @param int $id
     * @return void
     */
    public function edit($id)
    {
        //return 'edit';
        event(new EditEvent($this->name));
        $item = $this->findForEdit($id);
        return $this->editCrud($item);
    }


}
