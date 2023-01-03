<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Tir\Crud\Support\Requests\CrudRequest;

trait UpdateTrait
{

    public function update(CrudRequest $request, int|string $id)
    {
        $request->validated();
        $item = $this->model()->findOrFail($id);
        $item = $this->updateCrud($request, $id, $item);
        return $this->updateResponse($item);
    }


    final function updateCrud($request, $id, $item)
    {
        return DB::transaction(function () use ($request, $item) { // Start the transaction
            //TODO GetOnlyEditFields
            $item->update($request->only(collect($this->model()->getAllDataFields())->pluck('request')->flatten()->toArray()));

            $this->updateRelations($request, $item);

            DB::commit();

            return $item;
        });
    }



    final function updateRelations(Request $request, $item)
    {
        foreach ($this->model()->getEditFields() as $field) {
            if (isset($field->relation) && $field->multiple) {
                $data = $request->input($field->name);
                $item->{$field->relation->name}()->sync($data);
            }
        }
    }


    final function updateResponse($item): JsonResponse
    {
        $moduleName = $this->model()->getModuleName();
        $message = trans('core::message.item-updated', ['item' => trans("message.item.$moduleName")]); //translate message
        return Response::Json(
            [
                'id'      => $item->id,
                'changes' => $item->getChanges(),
                'updated' => true,
                'message' => $message,
            ]
            , 200);

    }

}
