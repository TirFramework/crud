<?php

namespace Tir\Crud\Controllers;

use Tir\Crud\Events\ForceDestroyEvent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

trait ActionTrait
{
    //TODO: how can add custom action ?
    protected $successful = 0;
    protected $error = 0;
    public function action(request $request)
    {
        $ids = $request->input('ids');

        switch ($request->input('action')) {

            case 'publish':
                $this->publishAction($ids);
            break;

            case 'unpublish':
                $this->publishAction($ids);
            break;

            case 'restore':
                $this->restoreAction($ids);
            break;

            case 'delete':
                $this->deleteAction($ids);
            break;

            case 'fullDelete':
                $this->fullDeleteAction($ids);
            break;
        }
        return null;
    }
   
    public function publishAction($ids)
    {
        $successful = 0;
        $error = 0;
        if($this->checkPermission('edit')){
            foreach ($ids as $id) {
                $item = $this->model::findOrFail($id);
                $item->status = 'published';
                if ($item->save()) {
                    $successful++;
                } else {
                    $error++;
                }
            }
            $message = trans('crud::message.item-multi-updated', ['item' => trans("message.item.$this->name"), 'count' => $successful]) . ' ' .
            trans('crud::message.item-multi-updated-error',  ['item' => trans("message.item.$this->name"), 'count' => $error]); //translate message
            Session::flash('message', "$message");
            return response()->json();
        }
    }

    public function unpublishAction($ids)
    {
        $successful = 0;
        $error = 0;
        if($this->checkPermission('edit')){
            foreach ($ids as $id) {
                $item = $this->model::findOrFail($id);
                $item->status = 'unpublished';
                if ($item->save()) {
                    $successful++;
                } else {
                    $error++;
                }
            }
            $message = trans('crud::message.item-multi-updated', ['item' => trans("message.item.$this->name"), 'count' => $successful]) . ' ' .
            trans('crud::message.item-multi-updated-error',  ['item' => trans("message.item.$this->name"), 'count' => $error]); //translate message
            Session::flash('message', "$message");
            return response()->json();
        }
    }

    public function restoreAction($ids)
    {
        $successful = 0;
        $error = 0;
        if($this->checkPermission('delete')){

            foreach ($ids as $id) {
                $item = $this->model::withTrashed()->findOrFail($id);
                if ($item->restore()) {
                    $successful++;
                } else {
                    $error++;
                }
            }
            $message = trans('crud::message.item-multi-restored', ['item' => trans("message.item.$this->name"), 'count' => $successful]) . ' ' .
                trans('crud::message.item-multi-restored-error',  ['item' => trans("message.item.$this->name"), 'count' => $error]); //translate message
            Session::flash('message', "$message");
            return response()->json();
        }
    }
    public function deleteAction($ids)
    {
        $successful = 0;
        $error = 0;
        if($this->checkPermission('destroy')){

            foreach ($ids as $id) {
                $item = $this->model::findOrFail($id);
                if ($item->delete()) {
                    $successful++;
                } else {
                    $error++;
                }
            }
            $message = trans('crud::message.item-multi-deleted', ['item' => trans("message.item.$this->name"), 'count' => $successful]) . ' ' .
                trans('crud::message.item-multi-deleted-error',  ['item' => trans("message.item.$this->name"), 'count' => $error]); //translate message
            Session::flash('message', "$message");
            return response()->json();
        }
    }
    public function fullDeleteAction($ids)
    {
        $successful = 0;
        $error = 0;
        if($this->checkPermission('forceDestroy')){

            foreach ($ids as $id) {
                $item = $this->model::withTrashed()->findOrFail($id);
                if ($item->forceDelete()) {
                    $successful++;
                } else {
                    $error++;
                }
            }
            $message = trans('crud::message.item-multi-deleted', ['item' => trans("message.item.$this->name"), 'count' => $successful]) . ' ' .
                trans('crud::message.item-multi-deleted-error',  ['item' => trans("message.item.$this->name"), 'count' => $error]); //translate message
            Session::flash('message', "$message");
            return response()->json();
        }
    }


    public function reorder(request $request)
    {
        if($this->checkPermission('index')){

            if ($request->input('id') !== null) {
                $id = $request->input('id');
                $position = $request->input('ordered');
                $item = $this->model::withTrashed()->findOrFail($id);
                $item->ordered = $position;
                if ($item->save()) {
                    $response = 'send response records updated goes here';
                    return response()->json($response);
                }
            } else {  //drag
                $count = 0;
                $ids = $request->input('data');
                if (count($ids)) {
                    foreach ($ids as $i => $key) {
                        $id = $key['id'];
                        $position = $key['position'];
                        $item = $this->model::withTrashed()->findOrFail($id);
                        $item->ordered = $position;
                        if ($item->save()) {
                            $count++;
                        }
                    }
                    $response = 'send response records updated goes here';
                    return response()->json($response);
                } else {
                    $response = 'send nothing to sort response goes here';
                    return response()->json($response);
                }
            }
        }
    }
}
