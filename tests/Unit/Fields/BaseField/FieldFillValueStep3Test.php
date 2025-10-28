<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Tests\Unit\Fields\BaseField\BaseFieldTestCase;

class FieldFillValueStep3Test extends BaseFieldTestCase
{
    /**
     * Test: No accessor configured - should not modify existing value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_no_accessor_configured()
    {
        $model = new MockEloquentModelStep3(['name' => 'John']);

        $field = Text::make('name');
        $field->get($model);

        // Should have extracted the raw value from Step 1
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John', $value);
    }

    /**
     * Test: Accessor configured and transforms existing value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_transforms_value()
    {
        $model = new MockEloquentModelStep3(['name' => 'john']);

        $field = Text::make('name');
        $field->accessor(function ($value) {
            return strtoupper($value);
        });
        $field->get($model);

        // Should have transformed the value using accessor
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('JOHN', $value);
    }

    /**
     * Test: Accessor configured but value is null - should call accessor with null
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_with_null_value()
    {
        $model = new MockEloquentModelStep3(['title' => 'Test']); // No 'name' field

        $field = Text::make('name'); // This field doesn't exist
        $field->accessor(function ($value) {
            return $value ?? 'DEFAULT';
        });
        $field->get($model);

        // Should have called accessor with null and returned default
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('DEFAULT', $value);
    }

    /**
     * Test: Accessor uses model context for transformation
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_uses_model_context()
    {
        $model = new MockEloquentModelStep3(['first_name' => 'John', 'last_name' => 'Doe']);

        $field = Text::make('first_name');
        $field->accessor(function ($value, $model) {
            return $value . ' ' . $model->last_name;
        });
        $field->get($model);

        // Should have used model context to create full name
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John Doe', $value);
    }

    /**
     * Test: Accessor returns array value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_returns_array()
    {
        $model = new MockEloquentModelStep3(['tags' => 'php,laravel']);

        $field = Text::make('tags');
        $field->accessor(function ($value) {
            return explode(',', $value);
        });
        $field->get($model);

        // Should have transformed string to array
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals(['php', 'laravel'], $value);
    }

    /**
     * Test: Accessor completely overrides value with model data
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_overrides_with_model_data()
    {
        $model = new MockEloquentModelStep3(['name' => 'John', 'computed_field' => 'Computed Value']);

        $field = Text::make('name');
        $field->accessor(function ($value, $model) {
            // Ignore the original value and use computed field
            return $model->computed_field;
        });
        $field->get($model);

        // Should have ignored original value and used computed field
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('Computed Value', $value);
    }

    /**
     * Test: Accessor transforms relational value from Step 2
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_transforms_relational_value()
    {
        // Create a model with author relation
        $author = new MockEloquentModelStep3(['id' => 1, 'name' => 'john doe']);

        $model = new MockEloquentModelStep3(['title' => 'Test Post']);
        $model->author = $author;

        $field = Text::make('author');
        $field->relation('author', 'name');
        $field->accessor(function ($value) {
            // $value will be ['john doe'] from relation
            return strtoupper($value[0]);
        });
        $field->get($model);

        // Should have transformed the relational value
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('JOHN DOE', $value);
    }

    /**
     * Test: Accessor returns null
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step3_accessor_returns_null()
    {
        $model = new MockEloquentModelStep3(['name' => 'John']);

        $field = Text::make('name');
        $field->accessor(function ($value) {
            return null; // Explicitly return null
        });
        $field->get($model);

        // Should have set value to null
        $value = $this->getPropertyValue($field, 'value');
        $this->assertNull($value);
    }
}

/**
 * Mock Eloquent Model for Step 3 testing
 */
class MockEloquentModelStep3 extends MockEloquentModel
{
    // Mock relation methods for testing
    public function categories()
    {
        return new BelongsToMany();
    }

    public function author()
    {
        return new BelongsTo();
    }

    public function category()
    {
        return new BelongsTo();
    }

    public function nonExistentRelation()
    {
        return new BelongsTo();
    }
}
