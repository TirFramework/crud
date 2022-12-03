<?php

namespace Tir\Crud\Support\Scaffold\Fields;

 class Text extends BaseField
{
     protected string $type = 'Text';
     protected array $relation;


     public function relation(string $name, string $field, string $primaryKey = 'id'):Text
     {
         $this->relation = ['name' => $name, 'field' => $field, 'key' => $primaryKey];
         return $this;
     }

 }
