<?php

namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;
use Tir\Crud\Scopes\OwnerScope;

trait BaseScaffold
{

    //Scaffolding

    private array $indexFields = [];
    private array $editFields = [];

    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

//    private object $fields;
    private $fields = [];
    public string $moduleName;
    protected array $validationRules = [];


    /**
     * The attribute can on / off localization for this scaffold
     *
     * @var bool
     */
    public bool $localization;

    function __construct()
    {
//        parent::__construct();

        $this->moduleName = $this->setModuleName();


        $this->addFieldsToScaffold();
        $this->setLocalization();
        $this->setValidationRules();
    }


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OwnerScope);
    }

    private function addFieldsToScaffold(): void
    {
        foreach ($this->setFields() as $input) {
            array_push($this->fields, $input->get());
        }
    }


    private function setLocalization(): void
    {
        if (!isset($this->localization)) {
            $this->localization = config('crud.localization');
        }

    }

    private function setValidationRules()
    {
        foreach ($this->getFields() as $field) {
            $this->validationRules[$field->name] = $field->roles;
        }
    }

    final function getFields(): array
    {
        return json_decode(json_encode($this->fields), false);
    }

    final function getModuleName(): string
    {
        return $this->moduleName;
    }

    final function getModel(): string
    {
        return $this->model;
    }

    final function getRouteName(): string
    {
        return $this->routeName;
    }

    final function getValidationRules()
    {
        return $this->validationRules;
    }

    final function getLocalization(): string
    {
        return $this->localization ? $this->moduleName . '::panel.' : '';
    }


    final function getIndexFields(): array
    {
        return Arr::where($this->getFields(), function ($value) {
            return $value->showOnIndex;
        });
    }

    final function getEditFields(): array
    {
        return Arr::where($this->getFields(), function ($value) {
            return $value->showOnEditing;
        });
    }

    final function getCreateFields(): array
    {
        return Arr::where($this->getFields(), function ($value) {
            return $value->showOnCreating;
        });
    }
}
