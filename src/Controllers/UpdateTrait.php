<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Tir\Crud\Support\Requests\CrudRequest;

trait UpdateTrait
{

    public function update(CrudRequest $request, int|string $id)
    {
        $item = $this->model()->findOrFail($id);
        $item =  DB::transaction(function () use ($request, $item) { // Start the transaction
            $item =  $this->updateCrud($request, $item);
            DB::commit();
            return $item;
        });
        return $this->updateResponse($item);

    }


    final function updateCrud($request, $item)
    {
        //TODO GetOnlyEditFields
        $requestData = $request->only(collect($this->model()->getAllDataFields())->pluck('request')->flatten()->toArray());

        //update fill
        $fillable = collect(
            Arr::dot($requestData)
        )->keys()->toArray();
        $item->fillable($fillable);

        if($item->getConnection()->getName() == 'mongodb'){

            $requestData = Arr::dot($request->all());
        }

        $item->update($requestData);

        $this->updateRelations($request, $item);

        return $item;
    }



    final function updateRelations(Request $request, $item)
    {
        foreach ($this->model()->getEditFields() as $field) {
            if (isset($field->relation) && $field->multiple) {
                $data = $request->input($field->name);
                if(isset($data)){
                    $item->{$field->relation->name}()->sync($data);
                }
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
