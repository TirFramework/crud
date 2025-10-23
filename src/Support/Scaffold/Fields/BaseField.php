<?php

namespace Tir\Crud\Support\Scaffold\Fields;

use Illuminate\Support\Arr;
use Tir\Crud\Support\Enums\FilterType;

abstract class BaseField
{
    use ValueHandler;
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
    protected $valueAccessor; // Callback to manipulate/override field value
    protected string|array $appends = []; // Columns this field depends on for computation




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
        $this->readonly = $value;
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

    public function relation(string $name, string $field, string $type = '', string $primaryKey = 'id'): static
    {
        $this->relation = (object) ['name' => $name, 'field' => $field, 'key' => $primaryKey, 'type' => $type,];
        return $this;
    }

    public function value(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Define a custom accessor to manipulate the field value
     *
     * Similar to Laravel's model accessors (getAttribute), this allows you to:
     * - Transform the original value from the model
     * - Override the value completely
     * - Apply formatting, calculations, or any custom logic
     *
     * The callback receives two parameters:
     * - $value: The original value extracted from the model
     * - $model: The full model instance for additional context
     *
     * Usage Examples:
     *
     * 1. Transform a value:
     *    ->accessor(fn($value) => strtoupper($value))
     *
     * 2. Override with model data:
     *    ->accessor(fn($value, $model) => $model->custom_field)
     *
     * 3. Format a date:
     *    ->accessor(fn($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null)
     *
     * 4. Concatenate fields:
     *    ->accessor(fn($value, $model) => $model->first_name . ' ' . $model->last_name)
     *
     * 5. Apply business logic:
     *    ->accessor(fn($value, $model) => $model->is_premium ? $value * 0.9 : $value)
     *
     * @param callable $callback Callback that receives ($value, $model) and returns transformed value
     * @return static
     */
    public function accessor(callable $callback): static
    {
        $this->valueAccessor = $callback;
        return $this;
    }

    /**
     * Mark this field to be appended to the model's serialization and specify column dependencies
     *
     * When enabled, this field will be included in the model's output even if it's not
     * a database column. Specify which columns must be selected for computation.
     *
     * Usage Examples:
     *
     * 1. Append with single dependency:
     *    Field::make('full_name')
     *        ->virtual()
     *        ->append('first_name')
     *        ->accessor(fn($value, $model) => strtoupper($model->first_name))
     *
     * 2. Append with multiple dependencies (array):
     *    Field::make('full_name')
     *        ->virtual()
     *        ->append(['first_name', 'last_name'])
     *        ->accessor(fn($value, $model) => $model->first_name . ' ' . $model->last_name)
     *
     * 3. Append with multiple dependencies (variadic):
     *    Field::make('total_price')
     *        ->virtual()
     *        ->append('price', 'quantity', 'tax_rate')
     *        ->accessor(fn($value, $model) => $model->price * $model->quantity * (1 + $model->tax_rate))
     *
     * @param string|array ...$columns Column name(s) this field depends on
     * @return static
     */
    public function appends(string|array ...$columns): static
    {
        // Column dependencies provided
        $normalized = $this->normalizeVariadicArgs(...$columns);

        // If it's not an array, convert to array
        if (!is_array($normalized)) {
            $normalized = [$normalized];
        }

        $this->appends = $normalized;
        return $this;
    }


    /**
     * Fill the field value from the model
     *
     * This method:
     * 1. Extracts the raw value from the model (regular field or relation)
     * 2. Applies the custom accessor if defined
     * 3. Sets the final value to $this->value
     *
     * @param mixed $model The model instance to extract value from
     */
    protected function fillValue($model): void
    {
        if (isset($model)) {
            // Step 1: Get the raw value from the model
            $value = Arr::get($model, $this->name);
            if (isset($value)) {
                $this->value = $value;
            }

            // Step 2: Handle relational values
            if (isset($this->relation)) {
                $value = $this->setRelationalValue($model);
                if (count($value) > 0) {
                    $this->value = $value;
                }
            }

            // Step 3: Apply custom accessor if defined
            if (isset($this->valueAccessor) && is_callable($this->valueAccessor)) {
                // Pass both the current value and the model to the accessor
                // This allows for flexible transformations and access to model data
                if(isset($this->value)) {
                    $this->value = call_user_func($this->valueAccessor, $this->value, $model);
                }else{
                    $this->value = call_user_func($this->valueAccessor, null, $model);
                }
            }
        }
    }

    public function get($dataModel)
    {
        $this->fillValue($dataModel);
        return (object) get_object_vars($this);
    }
}
