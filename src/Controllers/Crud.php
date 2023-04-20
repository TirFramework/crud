<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\Route;
use Tir\Crud\Support\Requests\CrudRequest;

trait Crud
{
    use IndexTrait, DataTrait, ShowTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, SelectTrait, DestroyTrait;

    private mixed $model;

    protected abstract function setModel(): string;

    public function __construct()
    {
        $this->modelInit();
        $this->addBasicsToRequest();
        $this->checkAccess();

        $this->middleware(function($request, $next){
            $this->CrudRequestInjector();
            $this->crudInit();
            $this->model()->scaffold();
            return $next($request);
        });


    }

    public function model()
    {
        return $this->model;
    }

    public function setFormRequest(): string
    {
        return '';
    }

    private function CrudRequestInjector(): void
    {
        $formRequest = $this->setFormRequest();
        if($formRequest){
            app()->singleton(CrudRequest::class, function ($app) use ($formRequest) {
                return new $formRequest;
            });
        }
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


    private function checkAccess()
    {
        if($this->model()->getAccessLevelStatus()){
            $this->middleware('acl:'.$this->model()->getModuleName());
         }

    }


}
