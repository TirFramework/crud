<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;
use Tir\Crud\Events\StoreEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;



trait StoreTrait
{
    /**
    * This function called from route. run an event and run createCrud functions
    * @return void
    */    
    public function store(Request $request)
    {
        event(new StoreEvent($this->name));

        $this->storeValidation($request, $this->validation);
        $request = $this->storeRequestManipulation($request);
        $item = $this->storeCrud($request);
        return $this->storeReturn($request, $item);
    }


    /**
     * Run validator on request
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public function storeValidation(Request $request, $validation)
    {
       $validation =  $this->validation;
        return Validator::make($request->all(), $validation)->validate();
    }

    /**
     * This function for manipulation on request data
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Request 
     */
    public function storeRequestManipulation(Request $request)
    {
        return $request;
    }

    /**
     * This function store crud and relations
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function storeCrud(Request $request)
    {

        // Add user_id to data for detect which user create item. this column use for ACL package and detect owner of item
        $request->merge(['user_id' => Auth::id()]);
        // Store model
        $item = $this->model::create($request->all());

        //Store relations
        foreach ($this->fields as $field) {
            if ((strpos($field->visible, 'c') !== false) && $field->type == 'relationM') {
                $data = $request->input($field->name);
                $item->{$field->relation}()->sync($data);
            }
        }

        return $item;
    }
    /**
     * This function redirect to view 
     * if user clicked save&close button function redirected user to index page
     * if user clicked on save button function redirected user to previous page
     *
     * @param \Illuminate\Http\Request $request
     * @param Object $item
     * @return redirect to url
     */
    public function storeReturn(Request $request, $item)
    {
        //if user select SAVE button url will like this /admin/xxx/1/edit
        //$url = ($request->input('save_close') ? route("$this->name.index") : route("$this->name.edit", [$this->name => $item->getKey()]));

        $message = trans('crud::message.item-created', ['item' => trans("message.item.$this->name")]); //translate message
        Session::flash('message', $message);
        if($request->input('save_close')){
            return Redirect::to(route("$this->name.index"));
        }else{
            return Redirect::back();
        }
    }



}
