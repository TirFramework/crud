<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Radio extends BaseField
{
    protected string $type = 'Radio';
    protected array $data;

    public function data(...$data): Radio
    {
        $this->data = $data;
        return $this;
    }


}
