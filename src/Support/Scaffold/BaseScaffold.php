<?php

namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tir\Authorization\Access;
use Tir\Crud\Scopes\OwnerScope;
use Tir\Crud\Support\Eloquent\HasAccessLevel;
use function PHPUnit\Framework\isEmpty;

trait BaseScaffold
{
//    use HasAccessLevel;

    //Scaffolding

    private array $indexFields = [];
    private array $editFields = [];
    protected bool $accessLevelControl = true;

    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

//    private object $fields;
    private static bool $accessLevel = false;
    private static string $action;
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
    protected array $actionsStatus = [
        'index' => true,
        'create'=>true,
        'edit'=>true,
        'destroy'=>true,
        'show'=>true
    ];




    public function scaffold($dataModel = null)
    {
        if($dataModel == null){
            $dataModel = new $this;
        }

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


    final function setActionsStatus($action, $status)
    {
        $this->actionsStatus[$action] = $status;
    }


    final function getActionsStatus():array
    {
        return $this->actionsStatus;
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
