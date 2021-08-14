<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Select extends BaseField
{
    protected string $type = 'select';
    protected array $data;
    protected bool $multiple;
    protected array $relation;

    /**
     * This function get data for select box
     *
     * @param array $data
     * @return $this
     */
    public function data(array $data): Select
    {
        $this->data = $data;
        return $this;
    }


    /**
     * Add multiple option to select box
     *
     * @param bool $check
     * @return $this
     */
    public function multiple(bool $check = true): Select
    {
        $this->multiple = $check;
        return $this;
    }

    public function relation(string $name, string $field): Select
    {
        $this->relation = ['name' =>$name, 'field'=>$field];
        return $this;
    }


    public function get($model = null): array
    {
        if(isset($this->relation)){
            $this->setDataRoute($model);
        }
        return parent::get($model);
    }


    private function setDataRoute($model)
    {
        $dataModel =  get_class($model->{$this->relation['name']}()->getModel());
        $dataModel = new $dataModel();
        $this->dataUrl = route('admin.' . $dataModel->getModuleName().'.select',['field'=>$this->relation['field']]);
    }


}