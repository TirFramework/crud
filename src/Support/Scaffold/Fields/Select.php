<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Select extends BaseField
{
    protected string $type = 'select';
    protected array $data;
    protected bool $multiple = false;
    protected array $relation;
    protected string $dataUrl;

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

    public function relation(string $name, string $field, string $primaryKey = 'id'): Select
    {
        $this->relation = ['name' => $name, 'field' => $field, 'key' => $primaryKey];
        return $this;
    }

    public function filter($items = []): BaseField
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

    public function get($model = null): array
    {
        if (isset($this->relation)) {
            $this->setDataRoute($model);
            $this->setDataFilter($model);
        }
        return parent::get($model);
    }


    private function setDataRoute($model)
    {
        $dataModel = get_class($model->{$this->relation['name']}()->getModel());
        $dataModel = new $dataModel();
        $this->dataUrl = route('admin.' . $dataModel->getModuleName() . '.select', ['field' => $this->relation['field']]);
    }

    private function setDataFilter($model)
    {
        if (!$this->filterable)
            return;

        if (count($this->filter))
            return;

        if (isset($this->relation)) {
            $filterModel = $model->first();

            //The filter model should have a record to get relation from model.
            if (isset($filterModel)) {
                $filterModel = $filterModel->{$this->relation['name']}()->distinct()->get()->map(function ($value) {
                    return [
                        'text'  => $value->{$this->relation['field']},
                        'value' => $value->{$this->relation['key']},
                    ];
                })->toArray();
                $this->filter = $filterModel;
            }


        }

    }

    private function setRelationalValue($model)
    {
        $value = [];

        if(isset($model->{$this->relation['name']})){

            if($this->multiple){
                $value = $model->{$this->relation['name']}->map(function ($value) {
                    return [
                        'text'  => $value->{$this->relation['field']},
                        'value' => $value->{$this->relation['key']},
                    ];
                })->toArray();
            }else{
                $value = [
                    'text'  => $model->{$this->relation['name']}->{$this->relation['field']},
                    'value' => $model->{$this->relation['key']}
                ];
            }

        }

        return $value;
    }

    protected function setValue($model)
    {
        if ($this->checkModelHasData($model)) {
            $this->value = $model->{$this->name};

            if(isset($this->relation)){
                $this->value = $this->setRelationalValue($model);
            }
        }


    }

}