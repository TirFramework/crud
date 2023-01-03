<?php

namespace Tir\Crud\Support\Scaffold\Fields;

use Illuminate\Support\Arr;

abstract class BaseField
{
    protected string $type;
    protected string $originalName;
    protected string $name;
    protected mixed $request;
    protected string $valueType = 'string';
    protected mixed $value = null;
    protected string $display;
    protected string $placeholder = '';
    protected string $class = '';
    protected string $group = 'all';
    protected int $col = 24;
    protected bool $disable = false;
    protected bool $readonly = false;
    protected string $defaultValue;
    protected bool $showOnIndex = true;
    protected bool $showOnDetail = true;
    protected bool $showOnCreating = true;
    protected bool $showOnEditing = true;
    protected bool $sortable = true;
    protected bool $searchable = false;
    protected array $rules = [];
    protected array $creationRules = [];
    protected array $updateRules = [];
    protected array $options = [];
    protected array $filter = [];
    protected bool $filterable = false;
    protected bool $multiple = false;
    protected array $comment = [];
    protected $dataSet = [];
    protected bool $additional = false;
    protected bool $dataField = true;



    public static function make(string $name):static
    {
        $obj = new static;
        $obj->init();
        $obj->originalName = $obj->name = $obj->request = $obj->className = $name;
        $obj->display = ucwords(str_replace('_', ' ', $name));
        return $obj;
    }

    protected function init():void{

    }

    public function display(string $value): BaseField
    {
        $this->display = $value;
        return $this;
    }

    public function class(string $name): BaseField
    {
        $this->class = $this->class . ' ' . $name;
        return $this;
    }

    public function placeholder(string $text): BaseField
    {
        $this->placeholder = $text;
        return $this;
    }

    public function col(string $number): BaseField
    {
        $this->col = $number;
        return $this;
    }

    public function comment(string $content, string $title = ''): BaseField
    {
        $this->comment = [
            'title' => $title,
            'content' => $content,
        ];
        return $this;
    }

    public function disable(bool $option = true): BaseField
    {
        $this->disable = $option;
        return $this;
    }

    public function readonly(bool $option = true): BaseField
    {
        $this->readonly = $option;
        return $this;
    }

    public function options($options = []): BaseField
    {
        $this->options = $options;
        return $this;
    }

    public function default(string $value): BaseField
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function showOnIndex($callback = true): BaseField
    {
        $this->showOnIndex = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function showOnCreating($callback = true): BaseField
    {
        $this->showOnCreating = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function showOnEditing(bool $callback = true): BaseField
    {
        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function showOnDetail(bool $callback = true): BaseField
    {
        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function hideFromIndex(bool $callback = true): BaseField
    {
        $this->showOnIndex = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideWhenCreating(bool $callback = true): BaseField
    {
        $this->showOnCreating = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideWhenEditing($callback = true): BaseField
    {
        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideFromDetail($callback = true): BaseField
    {
        $this->showOnDetail = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideFromAll($callback = true): BaseField
    {
        $this->showOnCreating =
        $this->showOnEditing =
        $this->showOnIndex =
        $this->showOnDetail =
            is_callable($callback) ? !call_user_func_array($callback, func_get_args())
                : !$callback;
        return $this;
    }

    public function onlyOnIndex(): BaseField
    {
        $this->showOnCreating = $this->showOnEditing = $this->showOnDetail = false;
        $this->showOnIndex = true;
        return $this;
    }

    public function onlyOnCreating(): BaseField
    {
        $this->showOnIndex = $this->showOnEditing = $this->showOnDetail = false;
        $this->showOnCreating = true;
        return $this;
    }

    public function onlyOnEditing(): BaseField
    {
        $this->showOnCreating = $this->showOnIndex = $this->showOnDetail = false;
        $this->showOnEditing = true;
        return $this;
    }

    public function onlyOnDetail(): BaseField
    {
        $this->showOnCreating = $this->showOnEditing = $this->showOnIndex = false;
        $this->showOnDetail = true;
        return $this;
    }

    public function sortable(bool $check = true): BaseField
    {
        $this->sortable = $check;
        return $this;
    }

    public function searchable(bool $check = true): BaseField
    {
        $this->searchable = $check;
        return $this;
    }

    public function rules(...$rules): BaseField
    {
        $this->creationRules = $this->updateRules = $this->rules = $rules;
        return $this;
    }

    public function creationRules(...$rules): BaseField
    {
        //TODO::need to fix in front-end
        $this->creationRules = $this->rules = $rules;
        return $this;
    }

    public function updateRules(...$rules): BaseField
    {
        $this->updateRules = $this->rules = $rules;
        return $this;
    }

    public function filter(...$items): BaseField
    {
        $this->filterable = true;
        $this->filter = $items;
        return $this;
    }

    protected function setValue($model): void
    {
        if(isset($model)){
            $this->value = Arr::get($model, $this->name);
        }
    }

    public function get($dataModel)
    {
        $this->setValue($dataModel);
        return (object) get_object_vars($this);
    }

}
