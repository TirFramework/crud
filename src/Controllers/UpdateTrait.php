<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Tir\Crud\Events\UpdateEvent;

trait UpdateTrait
{
    use ValidationTrait;

    public function update(Request $request, int $id): JsonResponse
    {
        $item = $this->model->findOrFail($id);
        $item->scaffold();

        $this->updateCrud($request, $id, $item);
        return $this->updateResponse($item);
    }


    private function updateCrud(Request $request, $id, $item)
    {
        return DB::transaction(function () use ($request, $item) { // Start the transaction

            $item->update($request->all());

            $this->updateRelations($request, $item);

            return $item;
        });
    }


    private function updateRelations(Request $request, $item)
    {
        foreach ($this->model->getCreateFields() as $field) {
            if (isset($field->relation) && $field->multiple) {
                $data = $request->input($field->name);
                $item->{$field->relation->name}()->sync($data);
            }
        }
    }


    private function updateResponse($item): JsonResponse
    {
        $moduleName = $this->model->getModuleName();
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
