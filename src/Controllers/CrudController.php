<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Tir\Authorization\access;
use Tir\Crud\Support\Scaffold\Crud;

class CrudController extends BaseController
{
    use IndexTrait, CreateTrait, DataTrait, StoreTrait, EditTrait, UpdateTrait;

    /**
     * Extend of CrudScaffold
     * @var string
     */
    protected mixed $scaffold;
    protected object $crud;

    protected array $relations = [];

    public function __construct()
    {
        $this->setCrud();
    }

    private function setCrud(): void
    {
        Crud::setScaffold($this->scaffold);
        $this->crud = Crud::get();
    }

    protected function checkAccess($action): string
    {
        if (class_exists(access::class)) {
            return access::check($this->crud->name, $action);
        }
        return 'allow';
    }

    protected function executeAccess($action): string
    {
        if (class_exists(access::class)) {
            return access::execute($this->crud->name, $action);
        }
        return 'allow';
    }


}
