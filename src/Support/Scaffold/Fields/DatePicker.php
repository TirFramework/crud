<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class DatePicker extends BaseField
{
    protected string $type = 'DatePicker';


    public function format($stringType): DatePicker
    {
        $this->options['dateFormat'] = $stringType;
        return $this;
    }


}
