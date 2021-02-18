<?php


namespace Tir\Crud\Support\Scaffold;


class Crud
{
    private string $name;

    private object $model;

    private string $routeName;

    private array $option;

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


    public static function getCrud() {
        if(!isset(self::$obj)) {
            self::$obj = new Crud();
        }
        return self::$obj;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return object
     */
    public function getModel(): object
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel(string $model): void
    {
        if (class_exists($model)) {
            $this->model = new $model;
        } else {
            echo($model . ' model not found');
        }

    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @param string $routeName
     */
    public function setRouteName(string $routeName): void
    {
        $this->routeName = $routeName;
    }

    /**
     * @return array
     */
    public function getOption(): array
    {
        return $this->option;
    }

    /**
     * @param array $option
     */
    public function addOption(array $option): void
    {
        $this->option = $option;
    }


}