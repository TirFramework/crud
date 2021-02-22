<?php


namespace Tir\Crud\Support\Scaffold;


final class Crud
{
    private string $name;

    private object $model;

    private string $routeName;

    private array $option;

    private array $fields;

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


    final static function get() {
        if(!isset(self::$obj)) {
            self::$obj = new Crud();
        }
        return self::$obj;
    }

    /**
     * @return string
     */
    final static function getName(): string
    {
        return static::get()->name;
    }

    /**
     * @param string $name
     */
    final static function setName(string $name): void
    {
        static::get()->name = $name;
    }

    /**
     * @return string
     */
    final static function getModel(): string
    {
        return static::get()->model;
    }

    /**
     * @param string $model
     */
    final static function setModel(string $model): void
    {
        if (class_exists($model)) {
            static::get()->model = new $model;
        } else {
            echo($model . ' model not found');
        }

    }

    /**
     * @return string
     */
    final static function getRouteName(): string
    {
        return static::get()->routeName;
    }

    /**
     * @param string $routeName
     */
    final static function setRouteName(string $routeName): void
    {
        static::get()->routeName = $routeName;
    }


    final static function setFields(array $fields){
        static::get()->fields = $fields;
    }

    final static function getFields(){
        return static::get()->fields;
    }


    /**
     * @return array
     */
    final function getOption(): array
    {
        return $this->option;
    }

    /**
     * @param array $option
     */
    final function addOption(array $option): void
    {
        $this->option = $option;
    }


}