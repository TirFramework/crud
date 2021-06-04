<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

/**
 * @property object $crud
 */
trait StoreTrait
{
    /**
     * This function called from route. run an event and run createCrud functions
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->storeValidation($request, $this->crud->validationRules);
        $item = $this->storeCrud($request);
        return $this->storeReturn($request, $item);
    }


    /**
     * This function for manipulation on request data
     * @param Request $request
     * @return Request
     */
    public function storeRequestManipulation(Request $request): Request
    {
        return $request;
    }

    /**
     * This function store crud and relations
     * @param Request $request
     * @return mixed
     */
    public function storeCrud(Request $request): mixed
    {

        return DB::transaction(function () use ($request) { // Start the transaction
            // Store model
            $item = $this->crud->model->create($request->all());

            //Store relations
            $this->storeRelations($request, $item);

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
     * @return RedirectResponse
     */
    public function storeReturn(Request $request, object $item): RedirectResponse
    {

        $message = trans('crud::message.item-created', ['item' => trans("message.item.$this->crud->name")]); //translate message
        Session::flash('message', $message);
        if ($request->input('save_close')) {
            return Redirect::to(route("$this->crud->name.index"));
        } elseif ($request->input('save_edit')) {
            return Redirect::to(route("$this->crud->name.edit", $item->getKey()));
        } else {
            return Redirect::back();
        }
    }

    private function storeRelations(Request $request, $item)
    {
        foreach ($this->crud->createFields as $field) {
            if ($field->type == 'manyToMany') {
                $data = $request->input($field->name);
                $item->{$field->relation[0]}()->sync($data);
            }
        }
    }

}
