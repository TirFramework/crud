<?php

namespace Tir\Crud\Support\Scaffold;


abstract class BaseScaffoldOld
{

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
    private array $fieldsArray;
    private array $fields = [];
    private string $name;
    private string $model;

    function __construct()
    {
        $this->name = $this->setName();
        $this->model = $this->setModel();
        $this->fieldsArray = $this->setFields();

        $this->addFieldsToScaffold();
        $this->setRouteName();
        $this->setLocalization();
    }

    protected abstract function setFields();

    private function addFieldsToScaffold(): void
    {
        foreach ($this->fieldsArray as $input) {
            array_push($this->fields, $input->get());
        }
    }

    private function setRouteName(): string
    {
        if (!isset($this->routeName)) {
            return $this->routeName = $this->name;
        }
    }

    private function setLocalization(): void
    {
        if (!isset($this->localization)) {
            $this->localization = config('crud.localization');
        }

    }

    final function getName(): string
    {
        return $this->name;
    }

    protected abstract function setName();

    final function getModel(): string
    {
        return $this->model;
    }

    protected abstract function setModel();

    final function getTable(): string
    {
        $model = new $this->model;
        return $model->getTable();
    }

    final function getRouteName(): string
    {
        return $this->routeName;
    }

    final function getLocalization(): string
    {
        return $this->localization;
    }

    final function getLocale(): string
    {
        return $this->localization ? $this->name . '::panel.' : '';
    }


}