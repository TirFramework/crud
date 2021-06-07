<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Tir\Authorization\access;

abstract class CrudController extends BaseController
{
    use IndexTrait, CreateTrait, DataTrait, StoreTrait, EditTrait, UpdateTrait;

    protected $model;
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
    }

    protected function checkAccess($module, $action): string
    {
        if (class_exists(access::class)) {
            return access::check($module, $action);
        }
        return 'allow';
    }

    protected function executeAccess($module, $action): string
    {
        if (class_exists(access::class)) {
            return access::execute($module, $action);
        }
        return 'allow';
    }


}
