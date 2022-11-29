<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Blank extends BaseField
{
    protected string $type = 'Blank';

    /**
     * Add display attribute to input
     *
     * @param string $value It will be value attribute of input
     * @return $this
     */
    public function value(string $value): BaseField
    {
        $this->value = $value;
        return $this;
    }

}
