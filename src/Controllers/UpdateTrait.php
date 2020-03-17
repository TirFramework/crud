<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;
use Tir\Crud\Events\UpdateEvent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

trait UpdateTrait
{
    /**
    * This function called from route. run an event and run update functions
    * @return void
    */    
    public function update(Request $request, $id)
    {
        event(new UpdateEvent($this->name));

        $item = $this->updateFind($id);
        $this->updateValidation($request, $item);
        $request = $this->updateRequestManipulation($request);
        $this->updateCrud($request, $item);
        return $this->updateReturn($request, $item);
    }


    /**
     * This function find an object model and if permission == owner return only owner item
     * @return eloquent
     */
    public function updateFind($id)
    {
        $item = $this->model::findOrFail($id);
        if ($this->permission == 'owner') {
            $item = $item->OnlyOwner();
        }
        return $item;
    }

    /**
     * Run validator on request
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public function updateValidation(request $request, $item)
    {
        $validation = $item->getValidation();
         return Validator::make($request->all(), $validation)->validate();

    }

    /**
     * This function for manipulation on request data
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Request 
     */
    public function updateRequestManipulation(request $request)
    {
        return $request;
    }

    /**
     * This function update crud and relations
     * @param \Illuminate\Http\Request $request
     * @return nothing
     */
    public function updateCrud(request $request, $item)
    {

        //update item
        $item->update($request->all());

        //update relation
        foreach ($this->fields as $field) {
            if ((strpos($field->visible, 'e') !== false)
                && isset($field->multiple)
                && isset($field->relation)) {
                $data = $request->input($field->name);
                $item->{$field->relation}()->sync($data);
            }
        }
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
    public function updateReturn(request $request, $item)
    {
        $url = ($request->input('save_close') ? route("$this->name.index") : route("$this->name.edit", [$this->name => $item->getKey()]));
        $message = trans('crud::message.item-updated', ['item' => trans("message.item.$this->name")]); //translate message
        Session::flash('message', $message);
        return redirect($url);
    }

}
