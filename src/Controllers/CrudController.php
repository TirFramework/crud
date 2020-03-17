<?php

namespace Tir\Crud\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tir\Acl\Acl;

class CrudController extends BaseController
{
    use IndexTrait, DataTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait;

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

    protected $options = [];

    protected $permission;

    
    public function __construct()
    {
        //set Crud name
        if (!$this->name) {
            //split route name and get keyName for route view
            $this->name = explode('.', Route::CurrentRouteName());
            $this->name = $this->name[0];
            $this->method = $this->name[1];
        }

        //Get Permission
        $this->permission = $this->getPermission($this->name, $this->method);
        //Check permission
        $this->checkPermission($this->name, $this->method);

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


        //options
        if ($this->options == null) {
            $this->options = [
                'datatableServerSide' => true,
            ];
        }

        /** All information about CRUD such as name, model, table, fields, etc,
         *  that used in Index, Data, Create, Store, and etc methods
         */
        $this->crud = (object) ['name' => $this->name, 'model' => $this->model, 'table' => $this->table, 'fields' => $this->fields, 'options' => $this->options, 'actions' => $this->actions];

    }

    private function getPermission($name, $action)
    {
        //if Acl package installed, add permission
        if (class_exists(\Tir\Acl\Permission::class)) {
            $permission = \Tir\Acl\Permission::list($name, $action);
        } else {
            $permission = 'all';
        }

        return $permission;
    }

    private function checkPermission($action)
    {
        if (isset($this->permission[$action])) {
            if ($this->permission[$action] == 'deny') {
                return false;
            }
            return true;
        }
    }

}
