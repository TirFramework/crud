<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Routing\Controller as BaseController;
use Tir\Authorization\Access;

abstract class CrudController extends BaseController
{
    use IndexTrait, DataTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, ValidationTrait, SelectTrait, DestroyTrait;

    protected abstract function setModel(): string;


    public function __construct()
    {
        $this->middleware('acl');
        // $this->middleware('setLocale');
        $this->modelInit();
        $this->validation();
    }


    private function modelInit(): void
    {
        $model = $this->setModel();
        $this->model = new $model;
        $this->model->scaffold();
    }



//    private function checkAccess($module, $action): string
//    {
//        if (class_exists(Access::class)) {
//            if (Access::check($module, $action) != 'deny') {
//                return true;
//            }
//        }
//    }
//
//    private function executeAccess($module, $action): string
//    {
//        if (class_exists(Access::class)) {
//            return Access::execute($module, $action);
//        }
//        return 'allow';
//    }


}
