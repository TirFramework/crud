<?php

namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
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



    public function scaffold($dataModel = null)
    {
        $this->moduleName = $this->setModuleName();
        $this->addFieldsToScaffold($dataModel);
        $this->setRules();

    }


    public static function boot()
    {
        parent::boot();
        self::creating(function($model){
            $model->user_id = auth()->id();
        });
//        static::addGlobalScope(new OwnerScope);
    }


    private function addFieldsToScaffold($dataModel): void
    {

        foreach ($this->setFields() as $input) {
            array_push($this->fields, $input->get($dataModel));
        }

    }

    private function setRules()
    {
        foreach ($this->getFields() as $field) {
            $this->rules[$field->name] = $field->rules;
        }
    }

    private function setCreationRules()
    {
        foreach ($this->getFields() as $field) {
            if (isset($field->creationRules))
                $this->creationRules[$field->name] = $field->creationRules;
        }
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
        return $this->setModuleName();
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
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnIndex) {
                array_push($fields, $field);
            }
        }
        return $fields;
    }

    final function getEditFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field){
            if ($field->showOnEditing){
                array_push($fields, $field);
            }
        }

        return $fields ;
    }


    final function getCreateFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnCreating) {
                array_push($fields, $field);
            }
        }

        return $fields;
    }

    final function getFieldByName($name)
    {
        foreach ($this->getIndexFields() as $field) {
            if($field->name == $name){
                return $field;
            }
        }
    }
}
