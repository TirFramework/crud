<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;


abstract class CrudController extends BaseController
{
    use IndexTrait, DataTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, ValidationTrait, SelectTrait, DestroyTrait;

    private $model;
    private $action;

    protected abstract function setModel(): string;


    public function __construct()
    {
        $this->modelInit();
        $this->addBasicsToRequest();
        $this->crudInit();
        $this->checkAccess();
        $this->model()->scaffold();
        $this->validation();
    }

    public function model()
    {
        return $this->model;
    }


    private function modelInit(): void
    {
        $model = $this->setModel();
        $this->model = new $model;
    }

    private function addBasicsToRequest()
    {
        $route = Route::getCurrentRoute();
        if($route) {
            $this->action = explode('@', Route::getCurrentRoute()->getActionName())[1];
            request()->merge([
                'crudModelName'=>$this->model(),
                'crudModuleName' => $this->model()->getModuleName(),
                'crudActionName'=>$this->action
            ]);
        }

    }

    protected function crudInit()
    {
    }

    protected function getAction()
    {
        return $this->action;
    }

    private function checkAccess()
    {
        if($this->model()->getAccessLevelStatus()){
            $this->middleware('acl');
    }

    }

}
