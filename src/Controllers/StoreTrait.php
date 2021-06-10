<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

/**
 * @property object $model
 */
trait StoreTrait
{
    /**
     * This function called from route. run an event and run createCrud functions
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        $this->storeValidation($request, $this->model->getCreationRules());
        $this->storeCrud($request);
        return $this->storeReturn($request);
    }


    /**
     * This function store crud and relations
     * @param Request $request
     * @return mixed
     */
    public function storeCrud(Request $request)
    {

        return DB::transaction(function () use ($request) { // Start the transaction
            // Store model
            $this->model->fill($request->all());
            $this->model->save();

            //Store relations
            $this->storeRelations($request);

            return $this->model;
        });
    }


    /**
     * This function redirect to view
     * if user clicked save&close button function redirected user to index page
     * if user clicked on save button function redirected user to previous page
     *
     * @param Request $request
     * @param Object $item
     * @return RedirectResponse
     */
    public function storeReturn(Request $request)
    {
        $moduleName = $this->model->moduleName;
        $message = trans('core::message.item-created', ['item' => trans("message.item.$moduleName")]); //translate message
        Session::flash('message', $message);
        if ($request->input('save_close')) {
            return Redirect::to(route("admin.$moduleName.index"));
        } elseif ($request->input('save_edit')) {
            return Redirect::to(route("admin.$moduleName.edit", $this->model->getKey()));
        } else {
            return Redirect::back();
        }
    }

    private function storeRelations(Request $request)
    {
        foreach ($this->model->getCreateFields() as $field) {
            if ($field->type == 'manyToMany') {
                $data = $request->input($field->name);
                $this->model->{$field->relation[0]}()->sync($data);
            }
        }
    }

}
