<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Blank extends BaseField
{
    protected string $type = 'Blank';
    protected bool $dataField = false;

    public function value(string $value): BaseField
    {
        $this->value = $value;
        return $this;
    }

    protected function setValue($model):void
    {
       //do not set value
    }




}
