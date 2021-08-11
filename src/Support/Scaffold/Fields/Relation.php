<?php

namespace Tir\Crud\Support\Scaffold\Fields;


 class Relation extends BaseField
{

     protected string $type = 'relation';
     protected array $relation;
     protected bool $multiple;


     /**
      * Add multiple option to select box
      *
      * @param bool $check
      * @return $this
      */
     public function multiple(bool $check): Relation
     {
         $this->multiple = $check;
         return $this;
     }

     public function relation(string $relationName, string $field): Relation
     {
         $this->relation = ['name' =>$relationName, 'field'=>$field];
         return $this;
     }



 }