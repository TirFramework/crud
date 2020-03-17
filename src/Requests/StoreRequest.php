<?php
namespace Tir\Crud\Requests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class StoreRequest
{

    /**
     * Run validator on request
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Validator
     */
    public function storeValidation(Request $request, $validation)
    {
        return Validator::make($request->all(), $validation)->validate();
    }

    /**
     * This function for manipulation on request data
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Facades\Validator
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
            if ((strpos($field->visible, 'c') !== false)
                && isset($field->multiple)
                && isset($field->relation)) {
                $data = $request->input($field->name);
                $item->{$field->relation}()->sync($data);
            }
        }

        return $item;
    }

    public function storeReturn($item, $request)
    {
        //if user select SAVE button url will like this /admin/xxx/1/edit
        $url = ($request->input('save_close') ? route("$this->name.index") : route("$this->name.edit", [$this->name => $item->getKey()]));

        $message = trans('crud::message.item-created', ['item' => trans("message.item.$this->name")]); //translate message
        Session::flash('message', $message);
        return redirect($url);
    }

}
