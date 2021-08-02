<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Tir\Authorization\access;

abstract class CrudController extends BaseController
{
    use IndexTrait, CreateTrait, DataTrait, StoreTrait, EditTrait, UpdateTrait;

    protected $item;
    protected array $relations = [];

    protected abstract function setModel(): string;

    public function __construct()
    {
        $this->middleware('acl');
        $this->modelInit();

    }

    private function modelInit(): void
    {
        $model = $this->setModel();
        $this->model = new $model;
        $this->model->scaffold();
    }

    protected function checkAccess($module, $action): string
    {
        if (class_exists(access::class)) {
            if (access::check($module, $action) != 'deny') {
                return true;
            }
        }
    }

    protected function executeAccess($module, $action): string
    {
        if (class_exists(access::class)) {
            return access::execute($module, $action);
        }
        return 'allow';
    }


    protected function getDataPermission(): array
    {
        $permission['index'] = $this->checkAccess($this->model->getModuleName(), 'index');
        $permission['edit'] = $this->checkAccess($this->model->getModuleName(), 'edit');
        $permission['destroy'] = $this->checkAccess($this->model->getModuleName(), 'destroy');

        return $permission;
    }


}
