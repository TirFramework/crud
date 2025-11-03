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

    private function getChildren($dataModel): array
    {
        $fields = [];
        foreach ($this->children as $input) {
            if ($this->readonly) {
                $input->readonly();
            }
            $fields[]  = $input->get($dataModel);
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
