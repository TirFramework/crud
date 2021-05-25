<?php

namespace Tir\Crud\Support\Scaffold\Fields;

 use Illuminate\Database\Eloquent\Model;

 class OneToMany extends BaseField
{

     protected string $type = 'oneToMany';

     protected string $relation;
     protected string $name;


     /**
      * This function set relation name
      * @param string $name
      * @return $this
      */
     public function relation(string $name):static
     {
         $this->relation = $name;

         $this->getModelFromRelation();
         return $this;
     }


     /**
      * Get model class of relation
      * @return string
      */
     private function getModelFromRelation():string
     {
         $this->model = get_class($this->relation->getModel());
     }

}