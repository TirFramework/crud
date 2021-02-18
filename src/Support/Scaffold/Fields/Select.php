<?php

namespace Tir\Crud\Support\Scaffold\Fields;

use Tir\Setting\Entities\Setting;

class Select extends BaseField
{
    protected string $type = 'select';
    protected array $data;
    protected bool $multiple;
    /**
     * This function get data for select box
     *
     * @param array $data
     * @return $this
     */
    public function data(array $data)
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
    public function multiple(bool $check){
        $this->multiple = $check;
        return $this;
    }



}