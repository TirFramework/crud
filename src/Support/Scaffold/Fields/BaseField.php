<?php

namespace Tir\Crud\Support\Scaffold\Fields;

use Illuminate\Support\Arr;
use Tir\Crud\Support\Enums\FilterType;

abstract class BaseField
{
    protected string $type;
    protected string $originalName;
    protected string $name;
    protected mixed $request;
    protected string $page;
    protected string $valueType = 'string';
    protected mixed $value;
    protected string $display;
    protected string $placeholder = '';
    protected string $class = '';
    protected int $col = 24;
    protected bool $disable = false;
    protected bool $readonly = false;
    protected mixed $defaultValue;
    protected bool $showOnIndex = true;
    protected bool $showOnDetail = true;
    protected bool $showOnCreating = true;
    protected bool $showOnEditing = true;
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected array $rules = [];
    protected array $creationRules = [];
    protected array $updateRules = [];
    protected array $options = [];
    protected array|object $data = [];
    protected array|object $filter = [];
    protected FilterType|string $filterType = FilterType::Select;
    protected bool $filterable = false;
    protected bool $multiple = false;
    protected array $comment = [];
    protected $dataSet = [];
    protected bool $additional = false;
    protected bool $fillable = true;
    protected bool $virtual = false;
    protected mixed $filterQuery;
    protected object $relation;
    protected string $className;




    public static function make(string $name): static
    {
        $obj = new static;
        $obj->init();
        $obj->originalName = $obj->name = $obj->request = $obj->className = $name;
        $obj->className = str_replace('.', '-', $name);
        $obj->display = ucwords(str_replace('_', ' ', $name));
        return $obj;
    }

    protected function init(): void
    {
    }
    public function page(string $page): static
    {
        $this->page = $page;
        return $this;
    }
    public function display(string $value): static
    {
        $this->display = $value;
        return $this;
    }

    public function class(string $name): static
    {
        $this->class = $this->class . ' ' . $name;
        return $this;
    }

    public function placeholder(string $text): static
    {
        $this->placeholder = $text;
        return $this;
    }

    public function col(string $number): static
    {
        $this->col = $number;
        return $this;
    }

    public function comment(string $content, string $title = ''): static
    {
        $this->comment = [
            'title' => $title,
            'content' => $content,
        ];
        return $this;
    }

    public function disable(bool $option = true): static
    {
        $this->disable = $option;
        return $this;
    }

    public function readonly(bool $option = true): static
    {
        $this->readonly = $option;
        return $this;
    }

    public function fillable(bool $option = true): static
    {
        $this->fillable = $option;
        return $this;
    }

    public function options($options = []): static
    {
        $this->options = $this->options + $options;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->defaultValue = $value;
        return $this;
    }


    public function virtual(bool $value = true): static
    {
        $this->virtual = $value;
        $this->fillable = !$value;
        return $this;
    }

    public function showOnIndex($callback = true): static
    {
        $this->showOnIndex = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function showOnCreating($callback = true): static
    {
        $this->showOnCreating = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function showOnEditing($callback = true): static
    {
        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function showOnDetail(bool $callback = true): static
    {
        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;
        return $this;
    }

    public function hideFromIndex(bool $callback = true): static
    {
        $this->showOnIndex = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideWhenCreating(bool $callback = true): static
    {
        $this->showOnCreating = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideWhenEditing($callback = true): static
    {
        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideFromDetail($callback = true): static
    {
        $this->showOnDetail = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function hideFromAll($callback = true): static
    {
        $this->showOnCreating =
            $this->showOnEditing =
            $this->showOnIndex =
            $this->showOnDetail =
            is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }

    public function onlyOnIndex(): static
    {
        $this->showOnCreating = $this->showOnEditing = $this->showOnDetail = false;
        $this->showOnIndex = true;
        return $this;
    }

    public function onlyOnCreating(): static
    {
        $this->showOnIndex = $this->showOnEditing = $this->showOnDetail = false;
        $this->showOnCreating = true;
        return $this;
    }

    public function onlyOnEditing($callback = true): static
    {
        $this->showOnCreating = $this->showOnIndex = $this->showOnDetail = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;

        $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;

        return $this;
    }

    public function onlyOnDetail($callback = true): static
    {
        $this->showOnCreating = $this->showOnIndex = $this->showOnEditing = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;

        $this->showOnDetail = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : $callback;

        return $this;
    }

    public function sortable(bool $check = true): static
    {
        $this->sortable = $check;
        return $this;
    }

    public function searchable(bool $check = true): static
    {
        $this->searchable = $check;
        return $this;
    }

    /**
     * Helper method to normalize variadic arguments
     * Handles both array and individual parameters
     */
    private function normalizeVariadicArgs(...$args): array|object
    {
        // Handle single array input: ->method(['item1', 'item2'])
        if (count($args) === 1 && (is_array($args[0]) || is_object($args[0]))) {
            return $args[0];
        }

        // Handle multiple parameters: ->method('item1', 'item2')
        return $args;
    }

    public function rules(...$rules): static
    {
        $flattenedRules = $this->normalizeVariadicArgs(...$rules);

        $this->creationRules = array_merge($this->creationRules, $flattenedRules);
        $this->updateRules = array_merge($this->updateRules, $flattenedRules);
        $this->rules = $flattenedRules;
        return $this;
    }

    public function creationRules(...$rules): static
    {
        $normalizedRules = $this->normalizeVariadicArgs(...$rules);
        $this->creationRules = array_merge($this->rules, $normalizedRules);
        return $this;
    }

    public function updateRules(...$rules): static
    {
        $normalizedRules = $this->normalizeVariadicArgs(...$rules);
        $this->updateRules = array_merge($this->rules, $normalizedRules);
        return $this;
    }

    public function data(...$data): static
    {
        $normalizedData = $this->normalizeVariadicArgs(...$data);
        $this->data = $normalizedData;
        $this->dataSet = collect($normalizedData)->pluck('label', 'value')->toArray();
        return $this;
    }


    public function filter(...$items): static
    {
        $this->filterable = true;

        $normalizedItems = $this->normalizeVariadicArgs(...$items);

        if (count($normalizedItems)) {
            $this->filter = $normalizedItems;
            return $this;
        }

        if (isset($this->data)) {
            $this->filter = $this->data;
            return $this;
        }

        return $this;
    }


    public function filterType(FilterType|string $type): static
    {
        $this->filterType = $type;
        return $this;
    }

    public function filterQuery(mixed $queryFunction): static
    {
        $this->filterQuery = $queryFunction;
        return $this;
    }


    /**
     * Add multiple option to select box
     *
     * @param bool $check
     * @return $this
     */
    public function multiple(bool $check = true): static
    {
        $this->multiple = $check;
        $this->valueType = 'array';

        return $this;
    }

    public function relation(string $field, string $name = null, string $primaryKey = 'id'): static
    {
        if (is_null($name)) {
            $name = $this->originalName;
        }
        $this->relation = (object) ['name' => $name, 'field' => $field, 'key' => $primaryKey];
        $this->multiple(true);
        $this->fillable(false);

        return $this;
    }


    private function setRelationalValue($model)
    {
        if (!isset($this->relation->name)) {
            throw new \Exception('Relation is not defined for field: ' . $this->name);
        }

        if (!isset($model->{$this->relation->name})) {
            throw new \Exception('For the field :' . $this->name . ' The Relation "' . $this->relation->name . '" not found on model');
        }
        return $model->{$this->relation->name}->map(function ($value) {
            return $value->{$this->relation->key};
        })->toArray();
    }
    protected function setValue($model): void
    {
        if (isset($model)) {
            $value = Arr::get($model, $this->name);
            if (isset($value)) {
                $this->value = $value;
            }

            if (isset($this->relation) && $this->multiple) {
                $value = $this->setRelationalValue($model);
                if (count($value) > 0) {
                    $this->value = $value;
                }

            }
        }

    }

    public function get($dataModel)
    {
        $this->setValue($dataModel);
        return (object) get_object_vars($this);
    }
}
