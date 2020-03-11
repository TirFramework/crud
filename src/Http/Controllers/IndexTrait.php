<?php 

namespace Amaj\Crud\Http\Controllers;

use Amaj\Crud\Events\CrudIndex;

trait IndexTrait
{
    /**
     * This function will be return a view and compact $crud to view
     *
     * @return view index.blade.php
     */
    public function index()
    {
        //event(new CrudIndex($this->name));
        return $this->indexCrud();

    }

    public function indexCrud()
    {
        
        if(view()->exists("$this->name::admin.index"))
        {
            return view("$this->name::admin.index",compact('crud'));
        }
        //dd($this->crud);
        return view("crud::admin.scaffold.index")->with('crud',$this->crud);
    }
}