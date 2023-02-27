<?php

namespace Tir\Crud\Support\Scaffold;

use Tir\Crud\Support\Scaffold\Fields\Button;

trait FieldsHelper
{

    private array $indexFields = [];
    private array $editFields = [];

    final function getFields()
    {
        return $this->fields;
    }


    final function getAllDataFields()
    {
        $allFields = [];
        $allFields = $this->getSubFields($this->fields, $allFields);
        return $allFields;
    }

    final function getIndexFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnIndex) {
                $fields[] = $field;
            }
        }
        return $fields;

    }

    final function getEditFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnEditing) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    final function getDetailFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnDetail) {
                $field->readOnly = true;
                $fields[] = $field;
            }
        }
        return $fields;
    }

    final function getCreateFields(): array
    {
        $fields = [];
        foreach ($this->getFields() as $field) {
            if ($field->showOnCreating) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    final function getFieldByName($name)
    {
        foreach ($this->getAllDataFields() as $field) {
            if ($field->name == $name) {
                return $field;
            }
        }
    }

    final function getSearchableFields(): array
    {
        $fields = [];
        foreach ($this->getIndexFields() as $field) {
            if ($field->searchable) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    private function getSubFields($fields, $allFields)
    {
        foreach ($fields as $field) {
            if (isset($field->subFields)) {
                $allFields = $this->getSubFields($field->subFields, $allFields);
            } elseif($field->fillable) {
                $allFields[] = $field;
            }
        }
        return $allFields;
    }


}
