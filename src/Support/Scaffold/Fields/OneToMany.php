<?php

namespace Tir\Crud\Support\Scaffold\Fields;

 use Illuminate\Database\Eloquent\Model;
 use Tir\Crud\Support\Scaffold\Crud;

 class OneToMany extends BaseField
{

     protected string $type = 'oneToMany';

     protected string $relationName;
     protected string $name;
     protected string $model;
     protected string $relationKey;


     /**
      * This function set relation name
      * @param string $name
      * @param string $key
      * @return $this
      */
     public function relation(string $name, string $key):static
     {
         $this->relationName = $name;
         $this->relationKey = $key;
//         $this->getModelFromRelation();
         return $this;
     }


     /**
      * Get model class of relation
      */
     private function getModelFromRelation()
     {
         $this->model = get_class($model->getModel());
     }

}