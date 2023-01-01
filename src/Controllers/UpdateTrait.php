<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

trait UpdateTrait
{
    use ValidationTrait;

    public function update(Request $request, int|string $id)
    {
        $item = $this->model()->findOrFail($id);
        $item = $this->updateCrud($request, $id, $item);
        return $this->updateResponse($item);
    }


    final function updateCrud(Request $request, $id, $item)
    {
        return DB::transaction(function () use ($request, $item) { // Start the transaction
            //TODO GetOnlyEditFields
            $item->update($request->only(collect($this->model()->getAllDataFields())->pluck('name')->flatten()->toArray()));

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
                'updated' => true,
                'message' => $message,
            ]
            , 200);

    }

}
