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
    protected array $rules = [];
    protected array $creationRules = [];
    protected array $updateRules = [];


    public function setLocale(): bool
    {
        return false;
    }

    function __construct()
    {
//        parent::__construct();


    }

    public function scaffold()
    {
        $this->moduleName = $this->setModuleName();
        $this->addFieldsToScaffold();
        $this->setRules();

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

    private function setRules()
    {
        foreach ($this->getFields() as $field) {
            $this->rules[$field->name] = $field->roles;
        }
    }

    private function setCreationRules()
    {
        foreach ($this->getFields() as $field) {
            if (isset($field->creationRules))
                $this->creationRules[$field->name] = $field->creationRules;
        }
    }


    private function setValue($fields)
    {
        foreach ($fields as $field) {
            //This file is a trait and we will use it in model so $this = model
            $field->value = $this->{$field->name};
        }
        return $fields;

    }


    final function setUpdateRules()
    {
        foreach ($this->getFields() as $field) {
            if (isset($field->updateRules))
                $this->rules[$field->name] = $field->updateRules;
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

    final function getCreationRules()
    {
        foreach ($this->getFields() as $field) {
            if ($field->creationRules)
                $this->rules[$field->name] = $field->creationRules;
        }
        return $this->rules;

    }

    final function getUpdateRules()
    {
        foreach ($this->getFields() as $field) {
            if ($field->updateRules)
                $this->rules[$field->name] = $field->updateRules;
        }
        return $this->rules;

    }


    final function getIndexFields(): array
    {
        return Arr::where($this->getFields(), function ($value) {
            return $value->showOnIndex;
        });
    }

    final function getEditFields(): array
    {
        $fields = Arr::where($this->getFields(), function ($value) {
            return $value->showOnEditing;
        });

        return $this->setValue($fields);

    }


    final function getCreateFields(): array
    {
        return Arr::where($this->getFields(), function ($value) {
            return $value->showOnCreating;
        });
    }
}
