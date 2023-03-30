<?php

namespace Tir\Crud\Support\Scaffold\Fields;

class Editor extends BaseField
{

   protected string $type = 'Editor';
   protected string $postUrl = '';
   protected string $basePath = '';
   protected int $height = 400;


   public function height(int $value): BaseField
   {
      $this->height = $value;
      return $this;
   }

   public function basePath(string $value): BaseField
   {
      $this->basePath = $value;
      return $this;
   }

   public function postUrl(string $value): BaseField
   {
      $this->postUrl = $value;
      return $this;
   }
}
