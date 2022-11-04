<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Group extends BaseField
{
    protected string $type = 'Group';
    protected array $subInputs = [];
    protected array $children = [];

    public function children($inputs):BaseField
    {
        $this->subInputs = $inputs;
        return $this;
    }
    private function getChildren($dataModel){
        foreach ($this->subInputs as $input){
            array_push($this->children, $input->get($dataModel));
        }
    }

    public function get($dataModel): array
    {
//        if(isset($dataModel)){
//            $this->setValue($dataModel);
//        }
        $this->getChildren($dataModel);
        return get_object_vars($this);
    }
}
