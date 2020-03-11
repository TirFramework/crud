<?php

namespace Amaj\Crud\Http\Controllers;

use Amaj\Crud\Events\CrudIndex;
use Amaj\Crud\Repositories\IndexRepository;
use Route;
use Str;
use Illuminate\Routing\Controller as BaseController;

class CrudController extends BaseController
{
    use IndexTrait;
    //The $name used for find Model, View, Controller and all crud system.
    protected $name;

    //The $model used for find model by $name
    //$model will be similar to App\Models\{model name}
    protected $model;

    protected $options = [];

    protected $actions = [];
    
    protected $crud = [];

    public function __construct()
    {
        //set Crud name
        if (!$this->name) {
            //split route name and get keyName for route view
            $this->name = explode('.',Route::CurrentRouteName());
            $this->name = $this->name[0];
        }


        //check model is exist in App\Modules\{model name}
        if(!$this->model){
            $this->model = 'App\Models\\'. ucfirst($this->name);
        }

        // check model is exist
        if (class_exists($this->model)) {
            $this->model = new $this->model;
        }else{
            return ($this->model . ' model not found');
        }

        //Get Table name from Model or set plural name
        if (isset($this->model->table)) {
           $this->table = $this->model->table;
        }else{
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

        $this->crud = (object)['name'=>$this->name, 'model'=>$this->model,'table'=>$this->table, 'fields'=>$this->fields,'options'=>$this->options, 'actions'=>$this->actions];
    }

}
