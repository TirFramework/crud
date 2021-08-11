<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;

abstract class CrudController extends BaseController
{
    use IndexTrait, DataTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, ValidationTrait, SelectTrait;

    protected abstract function setModel(): string;


    public function __construct()
    {
//        $this->middleware('acl');
        $this->request();
        $this->modelInit();
        $this->validation();

    }

    protected function request()
    {

    }

    private function modelInit(): void
    {
        $model = $this->setModel();
        $this->model = new $model;
        $this->model->scaffold();
    }


//
//    private function checkAccess($module, $action): string
//    {
//        if (class_exists(access::class)) {
//            if (access::check($module, $action) != 'deny') {
//                return true;
//            }
//        }
//    }
//
//    private function executeAccess($module, $action): string
//    {
//        if (class_exists(access::class)) {
//            return access::execute($module, $action);
//        }
//        return 'allow';
//    }
//
//
//    private function getDataPermission(): array
//    {
//        $permission['index'] = $this->checkAccess($this->model->getModuleName(), 'index');
//        $permission['edit'] = $this->checkAccess($this->model->getModuleName(), 'edit');
//        $permission['destroy'] = $this->checkAccess($this->model->getModuleName(), 'destroy');
//
//        return $permission;
//    }


}
