<?php

namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tir\Authorization\Access;
use Tir\Crud\Scopes\OwnerScope;
use function PHPUnit\Framework\isEmpty;

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

    protected string $indexable = 'allow';
    protected string $creatable = 'allow';
    protected string $editable = 'allow';
    protected string $deletable = 'allow';
    protected string $viewable = 'allow';



    public function scaffold($dataModel = null)
    {
        if($dataModel == null){
            $dataModel = new $this;
        }
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


    private function checkActionsAccess($moduleName)
    {
        if (class_exists(Access::class)) {

            $this->indexable = Access::check($moduleName, 'index');
            $this->viewable = Access::check($moduleName, 'view');
            $this->creatable = Access::check($moduleName, 'create');
            $this->editable = Access::check($moduleName, 'edit');
            $this->deletable = Access::check($moduleName, 'delete');

        }
    }


    final function setUpdateRules()
    {
        foreach ($this->getFields() as $field) {
            if (isset($field->updateRules))
                $this->rules[$field->name] = $field->updateRules;
        }
    }

    final function getActions():array
    {
        $this->checkActionsAccess($this->moduleName);
        return [
            'indexable'=>$this->indexable,
            'creatable'=>$this->creatable,
            'editable'=>$this->editable,
            'deletable'=>$this->deletable,
            'viewable'=>$this->viewable
        ];
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


    final function getSearchableFields()
    {
        $fields = [];
        foreach ($this->getIndexFields() as $field) {
            if ($field->searchable) {
                array_push($fields, $field);
            }
        }

        return $fields;

    }
}
