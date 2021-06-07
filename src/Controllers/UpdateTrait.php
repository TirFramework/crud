<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Tir\Crud\Events\UpdateEvent;

trait UpdateTrait
{
    use ValidationTrait;

    /**
     * @param Request $request
     * @param int $id
     * @return Redirect
     */
    public function update(Request $request, int $id)
    {
        $item = $this->model->findOrFail($id);
        $this->updateValidation($request, $this->model->getValidationRules(), $item);
        $this->updateCrud($request, $id, $item);
        return $this->updateReturn($request, $item);
    }


    /**
     * This function update crud and relations
     * @param Request $request
     * @param $item
     */
    public function updateCrud(Request $request, $id, $item)
    {


        return DB::transaction(function () use ($request, $item) { // Start the transaction


            $item->update($request->all());

            $this->updateRelations($request, $item);

            return $item;
        });
    }


    /**
     * This function redirect to view
     * if user clicked save&close button function redirected user to index page
     * if user clicked on save button function redirected user to previous page
     *
     * @param Request $request
     * @param Object $item
     * @return redirect to url
     */
    public function updateReturn(request $request, $item)
    {
        $name = $this->model->moduleName;

        $url = ($request->input('save_close') ? route("admin.$name.index") : route("admin.$name.edit", [$name => $item->getKey()]));
        $message = trans('core::message.item-updated', ['item' => trans("message.item.$name")]); //translate message
        Session::flash('message', $message);
        if ($request->input('save_close')) {
            return Redirect::to(route("admin.$name.index"));
        } else {
            return Redirect::to(route("admin.$name.edit", $item->getKey()))->with('tab', $request->input('tab'));
        }
    }

    private function updateRelations(Request $request, $item)
    {
        foreach ($this->model->getEditFields() as $field) {
            if ($field->type == 'manyToMany') {
                $data = $request->input($field->name);
                $item->{$field->relation[0]}()->sync($data);
            }
        }
    }

}
