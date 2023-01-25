<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\Route;

trait Crud
{
    use IndexTrait, DataTrait, ShowTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, SelectTrait, DestroyTrait;

    private mixed $model;

    protected abstract function setModel(): string;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->modelInit();
            $this->addBasicsToRequest();
            $this->crudInit();
            $this->checkAccess();
            $this->model()->scaffold();

            return $next($request);
        });
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
            $action = explode('@', Route::getCurrentRoute()->getActionName())[1];
            request()->merge([
                'crudModel'=>$this->model(),
                'crudModuleName' => $this->model()->getModuleName(),
                'crudActionName'=>$action
            ]);
        }

    }

    protected function crudInit()
    {
    }

//    protected function getAction()
//    {
//        return $this->action;
//    }

    private function checkAccess()
    {
        if($this->model()->getAccessLevelStatus()){
            $this->middleware('acl:'.$this->model()->getModuleName());
         }

    }


}
