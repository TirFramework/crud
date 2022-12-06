<?php

namespace Tir\Crud\Support\Scaffold\Fields;

class TextArea extends BaseField
{
    protected string $type = 'Textarea';
    protected int $row = 5;

    public function row($count){
        $this->row = $count;
        return $this;
    }
}
