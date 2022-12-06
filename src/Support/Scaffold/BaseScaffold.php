<?php

namespace Tir\Crud\Support\Scaffold;

trait BaseScaffold
{
    private array $indexFields = [];
    private array $editFields = [];
    protected bool $accessLevelControl = true;

    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

    public string $moduleName;
    protected array $rules = [];
    private array $fields = [];
    private array $buttons = [];

    protected array $actionsStatus = [
        'index' => true,
        'create'=>true,
        'edit'=>true,
        'destroy'=>true,
        'show'=>true
    ];



    public function scaffold($dataModel = null): static
    {
        $this->moduleName = $this->setModuleName();
        $this->addFieldsToScaffold($dataModel);
        $this->setRules();
        return $this;
    }

    public function getAccessLevelStatus(): bool
    {
        return $this->accessLevelControl;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            if(in_array('user_id',$model->fillable)){
                $model->user_id = auth()->id();
            }
        });
    }


    private function addFieldsToScaffold($dataModel): void
    {
        foreach ($this->setFields() as $field) {
            $this->fields[] = $field->get($dataModel);
        }
    }

    private function setRules(): void
    {
        foreach ($this->getFields() as $field) {
            $this->rules[$field->name] = $field->rules;
        }
    }

    private function getChildrenFields($field, $fields)
    {
        if(isset($field->children))
        {
            foreach ($field->children as $childField){
                if($childField->type != 'Group'){
                    $fields[] = $childField;
                }

                $fields = $this->getChildrenFields($childField, $fields);

            }
        }
        return $fields;
    }



    final function setActionsStatus($action, $status):bool
    {
        $this->actionsStatus[$action] = $status;
    }

    final function getActionsStatus():array
    {
        return $this->actionsStatus;
    }

    final function getFields()
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

    final function getCreationRules(): array
    {
        foreach ($this->getFields() as $field) {
            if ($field->creationRules)
                $this->rules[$field->name] = $field->creationRules;
        }
        return $this->rules;

    }

    final function getUpdateRules(): array
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
                if($field->type != 'Group'){
                    $fields[] = $field;
                }
                $fields = $this->getChildrenFields($field, $fields);


            }
        }
        return $fields;

    }


    final function getEditFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field){
            if ($field->showOnEditing){
                $fields[] = $field;
            }
        }
        return $fields ;
    }

    final function getCreateFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnCreating) {
                $fields[] = $field;
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

    final function getSearchableFields(): array
    {
        $fields = [];
        foreach ($this->getIndexFields() as $field) {
            if ($field->searchable) {
                $fields[] = $field;
            }
        }

        return $fields;

    }
}
