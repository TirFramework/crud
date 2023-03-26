<?php

namespace Tir\Crud\Support\Scaffold\Fields;

class Slug extends BaseField
{
    protected string $type = 'Slug';
    protected array $relation;


    public function relation(string $name, string $field, string $primaryKey = 'id'): Slug
    {
        $this->relation = ['name' => $name, 'field' => $field, 'key' => $primaryKey];
        return $this;
    }
}
