<?php

namespace Tir\Crud\Controllers;

use Tir\Crud\Support\HasCrudHooks;
use Illuminate\Support\Facades\Route;
use Tir\Crud\Support\Requests\CrudRequest;
use Tir\Crud\Support\Response\CrudResponse;

trait CrudInit
{
    use HasCrudHooks;


    private mixed $model;
    private mixed $scaffolder;

    protected abstract function setScaffolder(): string;

    public function __construct()
    {
        $this->scaffolderInit();
        // $this->checkAccess();
        $this->CrudRequestInjector();
        $this->crudInit();

        // Auto setup crud hooks if method exists
        if (method_exists($this, 'setup')) {
            $this->setup();
        }
    }

    protected function model()
    {
        return $this->model;
    }

    protected function scaffolder()
    {
        return $this->scaffolder;
    }
    protected function setRequest(): string
    {
        return '';
    }

    protected function setResponse(): string
    {
        return CrudResponse::class;
    }


    protected final function response(){
        $response = $this->setResponse();
        return new $response;
    }

    private function CrudRequestInjector(): void {
        $formRequestClass = $this->setRequest();
        if ($formRequestClass) {
            // Bind dependencies to container
            app()->when($formRequestClass)
                ->needs('model')
                ->give(fn() => $this->model());

            app()->when($formRequestClass)
                ->needs('scaffolder')
                ->give(fn() => $this->resolveScaffolder());

            app()->singleton(CrudRequest::class, $formRequestClass);
        }
    }

    private function scaffolderInit(): void
    {
        $s = $this->setScaffolder();
        $this->scaffolder = new $s;

        $m = $this->scaffolder->model();
        $this->model = new $m;
    }

    private function addBasicsToRequest(): void
    {
        $route = Route::getCurrentRoute();
        if($route) {
            $action = explode('@', Route::getCurrentRoute()->getActionName())[1];
            request()->merge([
                'crudScaffolder' => $this->scaffolder,
                'crudModel'=>$this->model,
                'crudModuleName' => $this->scaffolder->moduleName(),
                'crudActionName'=>$action
            ]);
        }

    }

    protected function crudInit(): void
    {
    }


    private function checkAccess(): void
    {
        // if($this->model()->getAccessLevelStatus() && config('crud.accessLevelControl') != 'off'){
        //     $this->middleware('acl:'.$this->model()->getModuleName());
        //  }

    }


}
