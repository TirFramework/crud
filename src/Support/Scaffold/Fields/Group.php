<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Group extends BaseField
{
    protected string $type = 'Group';
    protected array $subInputs = [];
    protected array $children = [];

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

    public function get($dataModel): array
    {
        $this->getChildren($dataModel);
        return parent::get($dataModel);
    }
}
