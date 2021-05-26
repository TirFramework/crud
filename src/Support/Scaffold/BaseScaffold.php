<?php

namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;

abstract Class BaseScaffold
{

    protected abstract function setName();
    protected abstract function setModel();
    protected abstract function setFields();

    private array $fieldsArray;
    private array $fields = [];
    private string $name;
    private string $model;


    /**
     * The attribute can on / off localization for this scaffold
     *
     * @var bool
     */
    public bool $localization;

    /**
     * The attribute specify the route name of scaffold.
     * @var string
     */
    public string $routeName;

    function __construct()
    {
        $this->name = $this->setName();
        $this->model = $this->setModel();
        $this->fieldsArray = $this->setFields();

        $this->addFieldsToScaffold();
        $this->setRouteName();
        $this->setLocalization();
    }


    private function addFieldsToScaffold(): void
    {
        foreach ($this->fieldsArray as $input){
            array_push($this->fields, $input->get());
        }
    }


    private function setLocalization():void {
        if(!isset($this->localization))
        {
            $this->localization = config('crud.localization');
        }

    }

    private function setRouteName():string
    {
        if(! isset($this->routeName))
        {
            return $this->routeName = $this->name;
        }
    }







    final function getFields():array {
        return json_decode(json_encode($this->fields), false);
    }

    final function getName():string
    {
        return $this->name;
    }

    final function getModel():string
    {
        return $this->model;
    }

    final function getTable():string
    {
        $model = new $this->model;
        return $model->getTable();
    }

    final function getRouteName(): string
    {
        return $this->routeName;
    }

    final function getLocalization():string
    {
        return $this->localization;
    }

    final function getLocale():string
    {
        return $this->localization ? $this->name.'::panel.' : '';
    }


}