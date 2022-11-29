<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Illuminate\Support\Arr;

class Additional extends BaseField
{
    protected string $type = 'Additional';
    protected array $children = [];
    protected array $template = [];

    public function children(...$inputs):BaseField
    {
        $this->children = $inputs;
        return $this;
    }

    private function getChildren($dataModel): array
    {
        $fields = [];
        if(isset($dataModel)){
            $fields = $this->getChildrenWithValue($dataModel);
        }else{
            $fields = $this->getChildrenWithValue($dataModel);
        }
        return $fields;
    }

    protected function getTemplate()
    {


    }

    protected function getChildrenWithValue($model): array
    {
        $fields = [];

        $values = Arr::get($model, $this->name) ?? [0];
        $index = 0;
        if(count($this->children) < 2){
            foreach ($values as $key => $value) {
                foreach ($this->children as $field) {
                    $field->name = $this->name.'.'.$index;
                    $fields[$index][] = $field->get($model);
                }
            $index++;
            }
        }else{
            foreach ($values as $value) {
                foreach ($this->children as $field) {
                    $field->name = $this->name.'.'.$index.'.'.$field->originalName;
                    $fields[$index][]  = $field->get($model);
                }
            $index++;
            }
        }

        return $fields;

    }


    public function get($dataModel): array
    {
        $this->children = $this->getChildren($dataModel);
        $this->template = $this->children[0];

        return parent::get($dataModel);
    }

}

