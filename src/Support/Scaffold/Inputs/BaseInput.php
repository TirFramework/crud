<?php

namespace Tir\Crud\Support\Scaffold\Inputs;


Class BaseInput
{
    public string $name;
    public string $visible;
    public string $validation;
    public array $other;

    public function __construct(string $name, string $visible, string $validation = null, array $other = null)
    {
        $this->name = $name;
        $this->visible = $visible;
        $this->validation = $validation;
        $this->other = $other;

        $this->mergePrams();
    }

    private function mergePrams(){
        $params = [$this->name, $this->visible, $this->validation];
         array_merge($params, $this->other);
    }
}