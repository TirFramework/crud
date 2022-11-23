<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Group extends BaseField
{
    protected string $type = 'Group';
    protected array $subInputs = [];
    protected array $children = [];

    public function children(...$inputs):BaseField
    {
        $this->subInputs = $inputs;
        return $this;
    }
    private function getChildren($dataModel){
        foreach ($this->subInputs as $input){
            if($this->additional){
                $input->name= $this->name.'.'.$input->name;
            }
            foreach($input->get($dataModel) as $field) {

                $this->children[] = $field;
            }
        }
    }

    public function get($dataModel): array
    {
        $this->getChildren($dataModel);
        return parent::get($dataModel);
    }
}
