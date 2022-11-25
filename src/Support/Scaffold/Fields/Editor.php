<?php

namespace Tir\Crud\Support\Scaffold\Fields;

 class Editor extends BaseField
{

     protected string $type = 'Editor';
     protected int $height = 400;


     public function height(int $value): BaseField
     {
        $this->height = $value;
        return $this;
     }

 }