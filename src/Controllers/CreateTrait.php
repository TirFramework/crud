<?php

namespace Tir\Crud\Controllers;

use Tir\Crud\Events\CrudCreate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;


trait CreateTrait
{
    /**
     * This function will be call createCrud() function and call an event.
     *
     * @return void
     */
    public function create()
    {
        //event(new CrudIndex($this->name));
        return $this->createCrud();
    }

    /**
     * Undocumented function
     *
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


    //   Store  /////////////////////////////////////////////////////////////////

    public function store(Request $request){
        //event(new CrudIndex($this->name));
        // $this->storeValidation($request);
        $data = $this->storeRequestManipulation($request);
        $result = $this->storeCrud($request,$data);
        return $this->storeReturn($result, $request);
    }


    public function storeValidation($request){
        return Validator::make($request->all(),$this->validator())->validate();
    }

    public function storeRequestManipulation($request){
        return $request->all();
    }

    public function storeCrud($request,$data){
        $error = 0;


        //store item
        // add user_id to data for detect which user create item. this column use for ACL system and detect owner of item
        $data['user_id'] = Auth::id();
        if(!$item = $this->model::create($data)){
            $error++;
        }

        //store relations
        foreach ($this->fields as $field) {
            if ((strpos($field->visible, 'c') !== false)) {
                if (isset($field->multiple)) {
                    if (isset($field->relation)) {
                        $data = $request->input($field->name);
                        if (!$item->{$field->relation}()->sync($data)) {
                            $error++;
                        }
                    }
                }
            }
        }

        return $result = (object)['error'=>$error , 'item'=>$item];
    }

    public function storeReturn($result ,$request){
        if($result->error == null){
            //if user select SAVE button url will like this /admin/xxx/1/edit
            $url = ($request->input('save_close') ? route("$this->name.index") : route("$this->name.edit", [ $this->name => $result->item->getKey()]));

            $message = trans('crud::message.item-created',['item'=>trans("message.item.$this->name")]); //translate message
            Session::flash('message', $message);
            return redirect($url);
        } else {
            $message = trans('crud::message.problem'); //translate message
            return  abort('500', $message);
        }
    }

//   End Store  //////////
}
