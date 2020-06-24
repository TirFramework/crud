<?php

namespace Tir\Crud\Services;

Class Crud
{

    protected $name;
    protected $fields=[];
    protected $additionalFields=[];
    protected $mergedFields;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        return $this->name = $name;
    }


    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return json_decode(json_encode($this->fields));
    }

    public function getAdditionalFields()
    {
        return $this->additionalFields;
    }

    public function addAdditionalFields($fields)
    {
        array_push($this->additionalFields, $fields);
    }

    public function mergeFields()
    {

        foreach($this->additionalFields as $additionalField){
            if ($additionalField['crudName'] == $this->name) {
                if(empty($additionalField['type']) ||
                    $additionalField['type'] == 'field'){
                    $this->addField($additionalField);
                }
            }
        }
    }

    private function addField($field)
    {
            $group = $field['group'] ?? 0;
            $tab = $field['tab'] ?? 0;
            $position = $field['position'] ?? 1000;
            $addField = [$field['fields']];
            array_splice($this->fields[$group]['tabs'][$tab]['fields'], $position, 0, $addField);

    }


}