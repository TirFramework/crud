<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Illuminate\Support\Arr;

class Select extends BaseField
{
    protected string $type = 'Select';
    protected array $data = [];
    protected object $relation;
    protected string $dataUrl;
    protected string $valueType = 'string';

    /**
     * This function get data for select box
     *
     * @param array $data
     * @return $this
     */
    public function data(...$data): Select
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

    public function relation(string $name, string $field, string $primaryKey = 'id'): Select
    {
        $this->relation = (object)['name' => $name, 'field' => $field, 'key' => $primaryKey];
        return $this;
    }

    public function filter(...$items): Select
    {
        $this->filterable = true;

        if (count($items)) {
            $this->filter = $items;
            return $this;
        }

        if (isset($this->data)) {
            $this->filter = $this->data;
            return $this;
        }

        return $this;

    }

    protected function init(): void
    {

    }

    public function get($dataModel)
    {

        if (isset($this->relation)) {
            $this->setDataRoute($dataModel);
            $this->setDataFilter($dataModel);
            $this->valueType = 'object';
        }
        if ($this->multiple) {
            $this->valueType = 'array';
        }
        $this->setDataSet();

        return parent::get($dataModel);
    }


    private function setDataSet()
    {
        if(count($this->dataSet)==0){
            $this->dataSet = collect($this->data)->pluck('label','value');
        }
    }

    private function setDataRoute($model)
    {

        if(isset($model)){
            $dataModel = get_class($model->{$this->relation->name}()->getModel());
//            $dataModel = new $dataModel();
//            $this->dataUrl = route('admin.' . $dataModel->getModuleName() . '.select', ['field' => $this->relation->field]);
        $this->data = $dataModel::select(
                $this->relation->field.' as label',
                $this->relation->key.' as value',
            )->get()->toArray();
        }
        $this->dataSet = $dataModel::select(
            $this->relation->field.' as label',
            $this->relation->key.' as value',
        )->get()->pluck('label', 'value')->toArray();

    }

    private function setDataFilter($filterModel)
    {
        if (!$this->filterable)
            return;

        if (count($this->filter))
            return;

        if (isset($this->relation)) {

            $filterModel = $filterModel->{$this->relation->name}()->getModel();

            $this->filter = $filterModel::select(
                $this->relation->field.' as label',
                $this->relation->key.' as value',
            )->get()->toArray();
        }

    }

    private function setRelationalValue($model)
    {
        return $model->{$this->relation->name}->map(function ($value) {
            return $value->{$this->relation->key};
        })->toArray();
    }

    protected function setValue($model):void
    {
        if(isset($model)){
            $value = Arr::get($model, $this->name);
            if(isset($value))
            {
                $this->value = $value ;
            }

            if(isset($this->relation) && $this->multiple){
                $this->value = $this->setRelationalValue($model);
            }
        }

    }


}
