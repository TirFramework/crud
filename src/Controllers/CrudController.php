<?php

namespace Tir\Crud\Controllers;

use Tir\Acl\Acl;
use Illuminate\Support\Str;
use Tir\Crud\Controllers\TrashTrait;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Controller as BaseController;

class CrudController extends BaseController
{
    //TODO: add show trait and method
    use IndexTrait, DataTrait, SelectTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, TrashTrait, DestroyTrait, ForceDestroyTrait;

    //The $name used for find Model, View, Controller and all crud system.
    protected $name;

    //The $name used for find which method called from route.
    protected $method;

    //The $model used for find model by $name
    //$model will be similar to App\Models\{model name}
    protected $model;

    protected $actions = [];

    protected $crud = [];

    protected $relations = [];

    protected $validation = [];

    protected $options = [];

    protected $fields = [];

    protected $additionalFields = [];

    protected $permission;

    
    public function __construct()
    {
        //set Crud name
        if (!$this->name) {
            //TODO: problem in console
            //split route name and get keyName for route view
            $routeName = explode('.', Route::CurrentRouteName());
            $this->name = $routeName[0];
            $this->method = $routeName[1];
        }

        //Get Permission
        $this->permission = $this->getPermission($this->name, $this->method);
        //Check permission
        if(!$this->checkPermission($this->method)){
            abort('403');
        }

        //check model is exist in App\Modules\{model name}
        if (!$this->model) {
            $this->model = 'App\Models\\' . ucfirst($this->name);
        }

        // check model is exist
        if (class_exists($this->model)) {
            $this->model = new $this->model;
        } else {
            return ($this->model . ' model not found');
        }

        //Get Table name from Model or set plural name
        if (isset($this->model->table)) {
            $this->table = $this->model->table;
        } else {
            $this->table = Str::plural($this->name);
        }

        // Get fields from model and convert to objective array
        $this->fields = $this->model->getFields();
        
        
        // check additional method exist
        if(method_exists($this->model,'getAdditionalFields')){
            $this->additionalFields = $this->model->getAdditionalFields();
        }

        

        //options
        if ($this->options == null) {
            $this->options = [
                'datatableServerSide' => true,
            ];
        }

        
        //validation
        $this->validation = $this->model->getValidation();


        //actions
        $this->actions = $this->model->getActions();


        /** All information about CRUD such as name, model, table, fields, etc,
         *  that used in Index, Data, Create, Store, and etc methods
         */
        $this->crud = (object) ['name' => $this->name, 'model' => $this->model, 'table' => $this->table, 'fields' => $this->fields, 'additionalFields' => $this->additionalFields, 'options' => $this->options, 'actions' => $this->actions, 'permission'=>$this->permission];

    }

    private function getPermission($name, $action)
    {
        //if Acl package installed, add permission
        if (class_exists(\Tir\Acl\Permission::class)) {
            $permission = \Tir\Acl\Permission::list($name, $action);
        } else {
            $permission = ['index'=>'all','show'=>'all','create'=>'all','edit'=>'all','delete'=>'all','fulldelete'=>'all'];
        }

        return $permission;
    }

    private function checkPermission($action)
    {
        $action = ($action == 'data') ? 'index' : $action;
        $action = ($action == 'select') ? 'index' : $action;
        $action = ($action == 'store') ? 'create' : $action;
        $action = ($action == 'update') ? 'edit' : $action;
        $action = ($action == 'restore') ? 'destroy' : $action;
        $action = ($action == 'trashData') ? 'trash' : $action;
        
        if (isset($this->permission[$action])) {
            if ($this->permission[$action] != 'deny') {
                return true;
            }
            return false;
        }
        return false;
    }

}
