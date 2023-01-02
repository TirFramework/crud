<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Illuminate\Support\Arr;

class Additional extends BaseField
{
    protected string $type = 'Additional';
    protected array $children = [];
    protected array $template = [];

    public function children(...$inputs):Additional
    {
        $this->children = $inputs;
        return $this;
    }

    public function request(...$schema): Additional
    {
        $this->request = $schema;
        return $this;
    }

    private function getChildren($dataModel): array
    {
        return $this->getChildrenWithValue($dataModel);
    }


    protected function getChildrenWithValue($model): array
    {
        $fields = [];

        $values = Arr::get($model, $this->name) ?? [0];
        $index = 0;

        //make json for multiple field
        if(count($this->children) < 2){
            foreach ($values as $key => $value) {
                foreach ($this->children as $field) {
                    $field->name = $this->name.'.'.$index;
                    $fields[$index][] = $field->get($model);
                }
            $index++;
            }
        }else{
            //make json for single field
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


    public function get($dataModel)
    {
        $this->children = $this->getChildren($dataModel);
        $this->template = $this->children[0];

        return parent::get($dataModel);
    }

}

