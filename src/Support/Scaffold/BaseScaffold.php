<?php

namespace Tir\Crud\Support\Scaffold;

use Tir\Crud\Support\Scaffold\Fields\Button;

trait BaseScaffold
{
    private array $indexFields = [];
    private array $editFields = [];
    protected bool $accessLevelControl = true;

    public static function boot()
    {
        parent::boot();

        self::creating(function($model){
            if(in_array('user_id',$model->fillable)){
                $model->user_id = auth()->id();
            }
        });
    }

    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

    protected function setButtons():array
    {
        return [];
    }

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
        $this->addButtonsToScaffold();
        $this->setRules();
        return $this;
    }

    public function getAccessLevelStatus(): bool
    {
        return $this->accessLevelControl;
    }

    private function addFieldsToScaffold($dataModel): void
    {
        foreach ($this->setFields() as $field) {
            $this->fields[] = $field->get($dataModel);
        }
    }

    private function addButtonsToScaffold(): void
    {
        foreach ($this->setButtons() as $button) {
            $this->buttons[] = $button->get(null);
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

    final function getButtons()
    {
        return json_decode(json_encode($this->buttons), false);
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

    final function getDetailFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field){
            if ($field->showOnDetail){
                $field->readOnly = true;
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

    final function getCreateButtons(): array
    {
        $buttons = [];
        foreach ($this->getButtons() as $button) {
            if ($button->showOnCreating) {
                $buttons[] = $button;
            }
        }
        return $buttons;
    }

    final function getDetailButtons(): array
    {
        $buttons = [];
        foreach ($this->getButtons() as $button) {
            if ($button->showOnDetail) {
                $buttons[] = $button;
            }
        }
        return $buttons;
    }

    final function getEditButtons(): array
    {
        $buttons = [];
        foreach ($this->getButtons() as $button) {
            if ($button->showOnEditing) {
                $buttons[] = $button;
            }
        }
        return $buttons;
    }

    final function getCreateElements(): array
    {
        return [
            'fields' => $this->getCreateFields(),
            'buttons' => $this->getCreateButtons(),
            'config' => []
        ];
    }

    final function getEditElements(): array
    {
        return [
            'fields' => $this->getEditFields(),
            'buttons' => $this->getEditButtons(),
            'config' => []
        ];
    }

    final function getDetailElements(): array
    {
        return [
            'fields' => $this->getDetailFields(),
            'buttons' => $this->getDetailButtons(),
            'config' => []
        ];
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
