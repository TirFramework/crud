<?php

namespace Tir\Crud\Support\Scaffold\Fields;


class Button extends BaseField
{
    protected string $type = 'Button';
    protected string $path;
    protected string $action;

    protected function setValue($model):void
    {
       //do not set value
    }

    public function action(string $name): static
    {
        $this->action = $name;
        return $this;
    }

    public function path(string $value): static
    {
        $this->path = $value;
        return $this;
    }
}
