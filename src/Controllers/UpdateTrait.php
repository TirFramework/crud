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
        return $this->updateResponse($request, $item);
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
        foreach ($item->getEditFields() as $field) {
            if ($field->type == 'manyToMany') {
                $data = $request->input($field->name);
                $item->{$field->relation[0]}()->sync($data);
            }
        }
    }


    private function updateResponse(Request $request, $item): JsonResponse
    {
        $moduleName = $this->model->getModuleName();

        $message = trans('core::message.item-updated', ['item' => trans("message.item.$moduleName")]); //translate message

        $redirectTo = null;

        if ($request->input('save_close')) {
            $redirectTo = Redirect::to(route("admin.$moduleName.index"));
        }

        return Response::Json(
            [
                'redirectTo' => $redirectTo,
                'message'    => $message,
            ]
            , 200);

    }

}
