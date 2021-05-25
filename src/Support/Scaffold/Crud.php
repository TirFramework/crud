<?php


namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;

final class Crud
{
    private string $name;

    private object $model;

    private string $routeName;

    private array $option;

    private $fields;

    private array $indexFields;

    private array $createFields;

    private array $editFields;

    private array $detailFields;

    private string $table;

    protected string $locale;
//    protected object $method;
//
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


    private static self $obj;


    public static function init() {
        if(!isset(self::$obj)) {
            self::$obj = new Crud();
        }
        return self::$obj;
    }


    /**
     * @param string $name
     */
    public static function setName(string $name): void
    {
        static::init()->name = $name;
    }

    /**
     * @param string $model
     */
    public static function setModel(string $model): void
    {
        if (class_exists($model)) {
            static::init()->model = new $model;
        } else {
            echo($model . ' model not found');
        }

    }


    /**
     * @param string $routeName
     */
    public static function setRouteName(string $routeName): void
    {
        static::init()->routeName = $routeName;
    }


    public static function setFields(array $fields){
        static::init()->fields = $fields;
    }



    public static function setLocalization(bool $check)
    {
        static::init()->locale = $check ? static::init()->name.'::panel.' : '';
    }


    /**
     * @return void
     */
    private function setIndexFields() {
        $this->indexFields = Arr::where($this->fields, function ($value) {
             return $value->showOnIndex;
        });
    }

    /**
     * @return void
     */
    private function setDetailFields(){
        $this->detailFields =  Arr::where($this->fields, function ($value) {
            return $value->showOnDetail;
        });
    }

    /**
     * @return void
     */
    private function setCreateFields() {
        $this->createFields =  Arr::where($this->fields, function ($value) {
            return $value->showOnCreate;
        });
    }

    /**
     * @return void
     */
    private function setEditFields() {
        $this->editFields =  Arr::where($this->fields, function ($value) {
            return $value->showOnEdit;
        });
    }

    private function setTable(){
        $this->table = $this->model->getTable();
    }


    public static function get(){
        static::init()->setIndexFields();
        static::init()->setCreateFields();
        static::init()->setEditFields();
        static::init()->setDetailFields();
        static::init()->setTable();
        return (object)get_object_vars(self::$obj);

    }


}