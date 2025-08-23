<?php
namespace Tir\Crud\Support\Scaffold\Traits;

trait ModelIntegration
{

    protected mixed $currentModel = null;

    // Magic method to access current model properties
    public function __get($property)
    {
        if ($this->currentModel) {
            // For Laravel Eloquent models, use isset to check if attribute exists
            if (isset($this->currentModel->$property)) {
                return $this->currentModel->$property;
            }
            // Also check if it's a defined attribute even if null
            if (
                method_exists($this->currentModel, 'getAttributes') &&
                array_key_exists($property, $this->currentModel->getAttributes())
            ) {
                return $this->currentModel->$property;
            }
        }
        return null;
    }

    public function __isset($property)
    {
        if ($this->currentModel) {
            // Check if attribute exists and is set
            if (isset($this->currentModel->$property)) {
                return true;
            }
            // Also check if it's a defined attribute even if null
            if (
                method_exists($this->currentModel, 'getAttributes') &&
                array_key_exists($property, $this->currentModel->getAttributes())
            ) {
                return true;
            }
        }
        return false;
    }

    // Helper methods for better readability
    protected function hasValue($property): bool
    {
        if ($this->currentModel) {
            // Check if attribute exists and is set
            if (isset($this->currentModel->$property)) {
                return true;
            }
            // Also check if it's a defined attribute even if null
            if (
                method_exists($this->currentModel, 'getAttributes') &&
                array_key_exists($property, $this->currentModel->getAttributes())
            ) {
                return true;
            }
        }
        return false;
    }

    protected function getValue($property, $default = null)
    {
        return $this->currentModel->$property ?? $default;
    }

    protected function currentModel()
    {
        return $this->currentModel;
    }
}
