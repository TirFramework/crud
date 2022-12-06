<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
trait DestroyTrait
{

    public function destroy($id): JsonResponse
    {
        $item = $this->model()->findOrFail($id);

//        DB::transaction(function () use ($item) { // Start the transaction
            $item->delete();
//        });

        return $this->deleteResponse();

    }



    private function deleteResponse(): JsonResponse
    {
        $moduleName = $this->model()->getModuleName();
        $message = trans('core::message.item-deleted', ['item' => trans("message.item.$moduleName")]);

        return Response::Json(
            [
                'deleted' => true,
                'message' => $message,
            ]
            , 200);

    }

     public function restore($id): JsonResponse
     {
         $moduleName = $this->model()->getModuleName();
         $item = $this->model()::onlyTrashed()->findOrFail($id);

         if ($item->restore()) {
             $message = trans('core::message.item-restored', ['item' => trans("message.item.$moduleName")]);
             return Response::Json(
                 [
                     'deleted' => true,
                     'message' => $message,
                 ]
                 , 200);
         } else {
             $message = trans('core::message.problem'); //translate message
             return Response::Json(
                 [
                     'deleted' => false,
                     'message' => $message,
                 ]
                 , 500);
         }
     }



}
