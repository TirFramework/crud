<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Group extends BaseField
{
    protected string $type = 'Group';
    protected array $subInputs = [];
    protected array $children = [];
    protected bool $dataField = false;

    public function children(...$inputs):Group
    {
        $this->children = $inputs;
        return $this;
    }
    private function getChildren($dataModel): array
    {
        $fields = [];
        foreach ($this->children as $input){
            $fields[]  = $input->get($dataModel);
        }
        $this->children = $fields;
        return $this-> children;
    }

    private function getChildrenRules(...$rules)
    {
        foreach ($this->children as $input)
        {
            if($input->rules){
                $rules[$input->name] =  $input->rules;
            }
        }
        $this->rules = $rules;
    }


    public function get($dataModel)
    {
        $this->getChildren($dataModel);
//        $this->getChildrenRules();
        return parent::get($dataModel);
    }
}
