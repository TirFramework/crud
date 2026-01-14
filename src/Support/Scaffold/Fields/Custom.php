<?php

namespace Tir\Crud\Support\Scaffold\Fields;

class Custom extends BaseField
{

    protected string $type = 'Custom';

    protected array $children = [];

    protected bool $fillable = true;

    protected bool $virtual = false;

    protected bool $showOnIndex = true;

    protected bool $shouldGetChildren = false;


    public function type(string $name): static
    {
        $this->type = $name;
        return $this;
    }

    public function children(...$inputs): static
    {
        $this->children = $inputs;

        $this->shouldGetChildren = true;
        $this->virtual = true;
        $this->showOnIndex = false;
        $this->fillable = false;

        return $this;
    }

    /**
     * Process children fields and prepare them for rendering.
     * Handles both Field objects (calling their get() method) and already processed data.
     * 
     * @param mixed $dataModel The model data to extract values from
     * @return array Processed children fields
     */
    private function getChildren($dataModel): array
    {
        $fields = [];
        foreach ($this->children as $input) {
            // Check if $input is a Field object (has get method)
            if (is_object($input) && method_exists($input, 'get')) {
                if ($this->readonly) {
                    $input->readonly();
                }
                $fields[] = $input->get($dataModel);
            } else {
                // If it's already processed data, just add it as-is
                $fields[] = $input;
            }
        }
        $this->children = $fields;
        return $this->children;
    }

    public function get($dataModel)
    {
        $this->getChildren($dataModel);
        return parent::get($dataModel);
    }
}
