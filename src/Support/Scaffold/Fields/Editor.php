<?php

namespace Tir\Crud\Support\Scaffold\Fields;

 class Editor extends BaseField
{

   protected string $type = 'Editor';
   protected string $uploadUrl;
   protected string $basePath = '/storage/';
   protected int $height = 400;


    public function height(int $value): static
   {
      $this->height = $value;
      return $this;
   }

   public function basePath(string $value): static
   {
      $this->basePath = $value;
      return $this;
   }

   public function uploadUrl(string $value): static
   {
      $this->uploadUrl = $value;
      return $this;
   }
}
