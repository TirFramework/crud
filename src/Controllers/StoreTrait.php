<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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
        $this->saveAdditional($request, $item);
        return $this->storeReturn($request, $item);
    }


    /**
     * Run validator on request
     * @param Request $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public function storeValidation(Request $request, $validation)
    {
       $validation =  $this->validation;
        return Validator::make($request->all(), $validation)->validate();
    }

    /**
     * This function for manipulation on request data
     * @param Request $request
     * @return Request
     */
    public function storeRequestManipulation(Request $request)
    {
        return $request;
    }

    /**
     * This function store crud and relations
     * @param Request $request
     * @return void
     */
    public function storeCrud(Request $request)
    {

        // Add user_id to data for detect which user create item. this column use for ACL package and detect owner of item
        $request->merge(['user_id' => Auth::id()]);
        // Store model
        $item = $this->model::create($request->all());

        //Store relations
        foreach ($this->fields as $group) {
            if ((strpos($group->visible, 'c') !== false)) {
                foreach ($group->tabs as $tab){
                    if ((strpos($tab->visible, 'c') !== false)) {
                        foreach ($tab->fields as $field) {
                            if ((strpos($field->visible, 'c') !== false) && $field->type == 'relationM') {
                                $data = $request->input($field->name);
                                $item->{$field->relation[0]}()->sync($data);
                            }
                        }
                    }
                }
            }
        }

        return $item;
    }


    public function saveAdditional(Request $request, $item)
    {
        return null;
    }

    /**
     * This function redirect to view
     * if user clicked save&close button function redirected user to index page
     * if user clicked on save button function redirected user to previous page
     *
     * @param Request $request
     * @param Object $item
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeReturn(Request $request, $item)
    {
        //if user select SAVE button url will like this /admin/xxx/1/edit
        //$url = ($request->input('save_close') ? route("$this->name.index") : route("$this->name.edit", [$this->name => $item->getKey()]));

        $message = trans('crud::message.item-created', ['item' => trans("message.item.$this->name")]); //translate message
        Session::flash('message', $message);
        if($request->requestType == 'ajax'){
            return $this->storeJsonReturn($item, $message);
        }
        if($request->input('save_close')){
            return Redirect::to(route("$this->name.index"));
        }elseif($request->input('save_edit')){
            return Redirect::to(route("$this->name.edit",$item->getKey()));
        }else{
            return Redirect::back();
        }
    }


    private function storeJsonReturn ($item, $message){
        return Response::Json(['message'=> $message, 'item' => $item]);
    }



}
