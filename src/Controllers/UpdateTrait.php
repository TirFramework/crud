<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;
use Tir\Crud\Events\UpdateEvent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

trait UpdateTrait
{
    /**
     * @param Request $request
     * @param int $id
     * @return Redirect
     */
    public function update(Request $request, int $id)
    {
        event(new UpdateEvent($this->name));

        $item = $this->updateFind($id);
        $this->updateValidation($request, $item);
        $request = $this->updateRequestManipulation($request);
        $this->updateCrud($request, $item);
        $this->updateAdditional($request, $item);
        return $this->updateReturn($request, $item);
    }


    /**
     * This function find an object model and if permission == owner return only owner item
     * @return object model
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
     * @param Request $request
     * @param $item
     */
    public function updateValidation(request $request, $item)
    {
        $validation = $item->getValidation();
         Validator::make($request->all(), $validation)->validate();

    }

    /**
     * This function for manipulation on request data
     * @param Request $request
     * @return Request
     */
    public function updateRequestManipulation(request $request)
    {
        return $request;
    }

    /**
     * This function update crud and relations
     * @param Request $request
     * @param $item
     */
    public function updateCrud(Request $request, $item)
    {

        //update item
        $item->update($request->all());

        //update relation
        foreach ($this->fields as $field) {
            if ((strpos($field->visible, 'e') !== false)&& $field->type == 'relationM') {
                $data = $request->input($field->name);
                $item->{$field->relation}()->sync($data);
            }
        }
    }


    /**
     * This method run saveAdditional function again and if we want different functionality in update,
     * we can override this function in update action
     * @param Request $request
     * @param $item
     * @return mixed
     */
    public function updateAdditional(Request $request, $item)
    {
        return $this->saveAdditional($request, $item);
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
        if($request->input('save_close')){
            return Redirect::to(route("$this->name.index"));
        }else{
            return Redirect::back();
        }
    }

}
