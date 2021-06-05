<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Tir\Authorization\access;
use Tir\Crud\Support\Scaffold\Crud;

class CrudController extends BaseController
{
    use IndexTrait, CreateTrait, DataTrait, StoreTrait, EditTrait, UpdateTrait;

//    protected mixed $scaffold;
//    protected object $crud;

    protected $model;
    protected $scaffoldName;
    protected array $relations = [];

    public function __construct()
    {
//        $this->setCrud();

    }

    private function setCrud(): void
    {
        Crud::setScaffold($this->scaffold);
        $this->crud = Crud::get();
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
