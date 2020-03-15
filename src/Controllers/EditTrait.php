<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

trait EditTrait
{

    //   Edit  /////////////////////////////////////////////////////////////////

    public function editAcl($id)
    {
        $item = null;
        $permission  = 'all';
        if ($permission == 'all') {
            $item = $this->model::findOrFail($id);
        } elseif ($permission == 'owner') {
            $item = $this->model::OnlyOwner()->findOrFail($id);
        }
        return $item;
    }

    public function editCrud($item)
    {
        $crud = (object) ['name' => $this->name, 'model' => $this->model, 'fields' => $this->fields];

        if (view()->exists("$this->name::admin.edit")) {
            return view("$this->name::admin.edit", compact('crud', 'item'));
        }


        return view("crud::scaffold.edit", compact('crud', 'item'));
    }

    public function edit($id)
    {
        $item = $this->model::findOrFail($id);
        //$item = $this->editAcl($id);
        return $this->editCrud($item);
    }

    // End Edit /////////


    //   Update  /////////////////////////////////////////////////////////////////

    public function update(Request $request, $id)
    {
        //$item = $this->updateAcl($id);

        //$this->updateValidation($request, $item);
        $data = $this->updateRequestManipulation($request);
        $item = $this->model->findOrFail($id);
        $error = $this->updateCrud($request, $data, $item);
        return $this->updateReturn($error, $item, $request);
    }


    // public function updateAcl($id)
    // {
    //     $permission =  Acl::executeAccess($this->name, 'edit');
    //     $item = null;
    //     //acl access check for owner or all data
    //     if ($permission == 'all') {
    //         $item = $this->model::findOrFail($id);
    //     } elseif ($permission == 'owner') {
    //         $item = $this->model::OnlyOwner()->findOrFail($id);
    //     }

    //     if ($item == null) {
    //         return abort('403');
    //     }
    //     return $item;
    // }

    public function updateValidation($request, $id)
    {
        //return Validator::make($request->all(), $this->validator($id))->validate();
    }

    public function updateRequestManipulation($request)
    {
        return $request->all();
    }

    public function updateCrud($request, $data, $item)
    {
        $error = 0;


        //update item
        if (!$item->update($data)) {
            $error++;
        }

        //update relation
        foreach ($this->fields as $field) {
            if ((strpos($field->visible, 'e') !== false)) {
                if (isset($field->multiple)) {
                    if (isset($field->relation)) {
                        $relationData = $request->input($field->name);
                        if (!$item->{$field->relation}()->sync($relationData)) {
                            $error++;
                        }
                    }
                }
            }
        }

        //return result
        return $error;
    }

    public function updateReturn($error, $item, $request)
    {
        if ($error == 0) {
            $url = ($request->input('save_close') ? route("$this->name.index") : route("$this->name.edit", [$this->name => $item->getKey()]));
            $message = trans('crud::message.item-updated', ['item' => trans("message.item.$this->name")]); //translate message
            Session::flash('message', $message);
            return redirect($url);
        } else {
            $message = trans('crud::message.problem'); //translate message
            return  abort('500', $message);
        }
    }
    //   End Update  /////////


}
