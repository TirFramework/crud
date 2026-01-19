<?php

namespace Tir\Crud\Support\Scaffold\Fields;


use Illuminate\Support\Arr;

class Additional extends BaseField
{
    protected string $type = 'Additional';
    protected array $children = [];

    protected bool $shouldGetChildren = false;
    protected array $template = [];
    protected bool $defaultChild = false;

    public function defaultChild($status = true):static {
        $this->defaultChild = $status;
        return $this;
    }
    public function children(...$inputs):static
    {
        $this->children = $inputs;
        return $this;
    }

    public function request(...$schema):static
    {
        $this->request = $schema;
        return $this;
    }

    private function getChildren($dataModel): array
    {
        return $this->getChildrenWithValue($dataModel);
    }


    protected function getChildrenWithValue($model): array
    {
        $fields = [];
        $values = [];
        if(Arr::get($model, $this->name) == null || Arr::get($model, $this->name) == [] ){
            $values = [0];
        }else{
            $values = Arr::get($model, $this->name) ?? [0];
        }

        $index = 0;
        foreach ($values as $value) {
            foreach ($this->children as $field) {
                // Clone the field to avoid data sharing between rows
                $clonedField = clone $field;
                
                // If field has children, deep clone them too using Reflection
                if (property_exists($clonedField, 'children')) {
                    $reflection = new \ReflectionProperty(get_class($clonedField), 'children');
                    $reflection->setAccessible(true);
                    $children = $reflection->getValue($clonedField);
                    
                    if (is_array($children)) {
                        $clonedChildren = [];
                        foreach ($children as $child) {
                            if (is_object($child)) {
                                $clonedChildren[] = clone $child;
                            } else {
                                $clonedChildren[] = $child;
                            }
                        }
                        $reflection->setValue($clonedField, $clonedChildren);
                    }
                }
                
                // Recursively replace * with index in field and all nested children
                $this->replaceWildcardInField($clonedField, $index);
                
                if($this->readonly){
                    $clonedField->readonly();
                }
                $fields[$index][] = $clonedField->get($model);
                unset($clonedField->value);
            }
            $index++;
        }

        return $fields;
    }

    /**
     * Recursively replace wildcard (*) with index in field name and all nested children.
     * This handles fields with nested children (like Custom fields) automatically.
     * 
     * @param object $field The field object to process
     * @param int $index The row index to replace the wildcard with
     * @return void
     */
    private function replaceWildcardInField($field, int $index): void
    {
        // Replace * in the field's own name
        if (property_exists($field, 'originalName')) {
            $field->name = str_replace('*', $index, $field->originalName);
        }
        
        // If field has children property, recursively process them using Reflection
        if (property_exists($field, 'children')) {
            try {
                $reflection = new \ReflectionProperty(get_class($field), 'children');
                $reflection->setAccessible(true);
                $children = $reflection->getValue($field);
                
                if (is_array($children)) {
                    foreach ($children as $childField) {
                        if (is_object($childField)) {
                            $this->replaceWildcardInField($childField, $index);
                        }
                    }
                }
            } catch (\ReflectionException $e) {
                // If reflection fails, skip children processing
            }
        }
    }


    public function get($dataModel)
    {
        $this->children = $this->getChildren($dataModel);
        $this->template = $this->children[0];
        if(!Arr::get($dataModel, $this->name) && !$this->defaultChild){
            $this->children = [];
        }
        return parent::get($dataModel);
    }

}

