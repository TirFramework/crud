<?php

namespace Tir\Crud\Support\Scaffold\Fields;

class Custom extends BaseField
{

    protected string $type = 'custom';

    public function type(string $name): static
    {
        $this->type = $name;
        return $this;
    }


}