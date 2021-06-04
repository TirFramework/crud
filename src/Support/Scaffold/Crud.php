<?php


namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;

final class Crud
{
    private string $name;

    protected mixed $model;

    private string $routeName;

    private array $option;

    private array $fields = [];

    private array $indexFields;

    private array $createFields;

    private array $editFields;

    protected array $detailFields;

    private string $table;

    protected string $locale;

    protected array $validationRules = [];

//    protected object $method;
//
//
//    protected $actions = [];
//
//    protected $crud = [];
//
//    protected $relations = [];
//
//
//    protected $options = [];
//
//    protected $fields = [];


    private static Crud $obj;


    public static function init(): Crud
    {
        if(!isset(self::$obj)) {
            self::$obj = new Crud();
        }
        return self::$obj;
    }

    public static function setScaffold($scaffold)
    {
        $scaffold = new $scaffold;

        Crud::setModel($scaffold->getModel());
        Crud::setName($scaffold->getName());
        Crud::setRouteName($scaffold->getRouteName());
        Crud::setFields($scaffold->getFields());
        Crud::setLocalization($scaffold->getLocalization());
        Crud::setIndexFields();
        Crud::setCreateFields();
        Crud::setEditFields();
        Crud::setDetailFields();
        Crud::setTable();
        Crud::setValidationRules();
    }


    /**
     * @param string $name
     */
    public static function setName(string $name): void
    {
        Crud::init()->name = $name;
    }

    /**
     * @param string $model
     */
    public static function setModel(string $model): void
    {
        if (class_exists($model)) {
            Crud::init()->model = new $model;
        } else {
            echo($model . ' model not found');
        }

    }


    /**
     * @param string $routeName
     */
    public static function setRouteName(string $routeName): void
    {
        Crud::init()->routeName = $routeName;
    }


    public static function setFields(array $fields){
        Crud::init()->fields = $fields;
    }



    public static function setLocalization(bool $check)
    {
        Crud::init()->locale = $check ? Crud::init()->name . '::panel.' : '';
    }


    /**
     * @return void
     */
    private static function setIndexFields() {
        Crud::init()->indexFields = Arr::where(Crud::init()->fields, function ($value) {
             return $value->showOnIndex;
        });
    }

    /**
     * @return void
     */
    private static function setDetailFields(){
        Crud::init()->detailFields =  Arr::where(Crud::init()->fields, function ($value) {
            return $value->showOnDetail;
        });
    }

    /**
     * @return void
     */
    private static function setCreateFields() {
        Crud::init()->createFields =  Arr::where(Crud::init()->fields, function ($value) {
            return $value->showOnCreating;
        });
    }

    /**
     * @return void
     */
    private static function setEditFields()
    {
        Crud::init()->editFields = Arr::where(Crud::init()->fields, function ($value) {
            return $value->showOnEditing;
        });
    }

    private static function setTable()
    {
        Crud::init()->table = Crud::init()->model->getTable();
    }

    private static function setValidationRules()
    {
        foreach (Crud::init()->fields as $field) {
            Crud::init()->validationRules[$field->name] = $field->roles;
        }

    }


    public static function get()
    {
        return (object)get_object_vars(self::$obj);
    }




}