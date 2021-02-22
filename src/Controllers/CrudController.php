<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Str;
use Tir\Crud\Controllers\TrashTrait;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Controller as BaseController;
use Tir\Crud\Support\Scaffold\Crud;

class CrudController extends BaseController
{
    use IndexTrait;

    protected string $model;
    protected string $scaffold;

    public function __construct()
    {
        $this->scaffold = $this->model::$scaffold;
        Crud::setModel($this->model);
        Crud::setName($this->scaffold::getCrudName());
        Crud::setRouteName($this->scaffold::getRouteName());
        Crud::setFields($this->scaffold::getFields());
    }
//    //TODO: add show trait and method
//    use IndexTrait, DataTrait, SelectTrait, CreateTrait, StoreTrait, EditTrait, UpdateTrait, TrashTrait, DestroyTrait, ForceDestroyTrait, ActionTrait;
//
//    //The $name used for find Model, View, Controller and all crud system.
//    protected $name;
//
//    //The $name used for find which method called from route.
//    protected $method;
//
//    //The $model used for find model by $name
//    //$model will be similar to App\Models\{model name}
//    protected $model;
//
//    protected $routeName;
//
//    protected $actions = [];
//
//    protected $crud = [];
//
//    protected $relations = [];
//
//    protected $validation = [];
//
//    protected $options = [];
//
//    protected $fields = [];
//
//
//
//
//    public function __construct()
//    {
//
//        $crud = resolve('Crud');
//
//        //set Crud name
//        if (!$this->name) {
//            //TODO: problem in console
//            //split route name and get keyName for route view
//            $routeName = explode('.', Route::CurrentRouteName());
//            $this->name = $routeName[0];
//
//            //Update crud singleton
//            $crud->setName($this->name);
//            //$this->method = $routeName[1];
//
//
//        }
//
//        // check model is exist
//        if (class_exists($this->model)) {
//            $this->model = new $this->model;
//        } else {
//            echo($this->model . ' model not found');
//        }
//
//        //Get route name from model
//        $this->routeName = $this->model::$routeName;
//
//        //Get Table name from Model or set plural name
//        if (isset($this->model->table)) {
//            $this->table = $this->model->table;
//        } else {
//            $this->table = Str::plural($this->name);
//        }
//
//
//        // Get fields from model and convert to objective array
//        $this->fields = $this->model->getFields();
//
//
//        // check additional method exist
//        if (method_exists($this->model, 'getAdditionalFields')) {
//            $this->additionalFields = $this->model->getAdditionalFields();
//        }
//
//
//        //options
//        if ($this->options == null) {
//            $this->options = [
//                'datatableServerSide' => true,
//            ];
//        }
//
//
//        //validation
//        $this->validation = $this->model->getValidation();
//
//
//        //actions
//        $this->actions = $this->model->getActions();
//
//        //add other packages fields to crud fields
//        // event(new PrepareFieldsEvent());
//
//        $crud->setFields($this->fields);
//        $crud->mergeFields();
//        $this->fields = $crud->getFields();
//
//        /** All information about CRUD such as name, model, table, fields, etc,
//         *  will used in Index, Data, Create, Store, and etc methods
//         */
//
//        $this->crud = (object)['name'             => $this->name,
//                               'model'            => $this->model,
//                               'routeName'        => $this->routeName,
//                               'table'            => $this->table,
//                               'fields'           => $this->fields,
//                               'additionalFields' => $this->additionalFields,
//                               'options'          => $this->options,
//                               'actions'          => $this->actions,
//                               'permission'       => $this->permission];
//
//
//    }


}
