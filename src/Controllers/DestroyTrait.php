<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
trait DestroyTrait
{

    public function destroy($id)
    {
        $item = $this->model->accessLevel()->findOrFail($id);

        DB::transaction(function () use ($item) { // Start the transaction
            $item->delete();
        });

        return $this->deleteResponse();

    }



    private function deleteResponse(): JsonResponse
    {
        $moduleName = $this->model->getModuleName();
        $message = trans('core::message.item-deleted', ['item' => trans("message.item.$moduleName")]); //translate message

        return Response::Json(
            [
                'deleted' => true,
                'message' => $message,
            ]
            , 200);

    }

    // public function restore($id){

    //     $item = $this->findForRestore($id);


    //     if ($item->restore()) {
    //         $message = trans('core::message.item-restored', ['item' => trans("message.item.$this->name")]); //translate message
    //         Session::flash('message', $message);
    //         return Redirect::back();
    //     } else {
    //         $message = trans('core::message.problem'); //translate message
    //         Session::flash('error', $message);
    //         return Redirect::back();
    //     }
    // }


    //  /**
    //  * This function find an object model and if permission == owner return only owner item
    //  * @return eloquent
    //  */
    // public function findForRestore($id)
    // {
    //     $items = $this->model::onlyTrashed()->findOrFail($id);
    //     if($this->permission == 'owner'){
    //         $items = $items->OnlyOwner();
    //     }
    //     return $items;
    // }

}
