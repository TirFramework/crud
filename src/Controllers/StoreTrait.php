<?php

namespace Tir\Crud\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

trait StoreTrait
{
    public function store(Request $request): JsonResponse
    {
        $item = $this->storeCrud($request);
        return $this->storeResponse($item);
    }


    /**
     * This function store crud and relations
     * @param Request $request
     * @return mixed
     */
    final function storeCrud(Request $request)
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


    final function storeResponse($item): JsonResponse
    {
        $moduleName = $this->model->getModuleName();
        $message = trans('core::message.item-created', ['item' => trans("message.item.$moduleName")]); //translate message

        return Response::Json(
            [
                'id' => $item->id,
                'created' => true,
                'message'    => $message,
            ]
            , 200);

    }

    final function storeRelations(Request $request)
    {
        foreach ($this->model->getCreateFields() as $field) {
            if (isset($field->relation) && $field->multiple) {
                $data = $request->input($field->name);
                $this->model->{$field->relation->name}()->sync($data);
            }
        }
    }

}
