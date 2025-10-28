<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Illuminate\Database\Eloquent\Model;

/**
 * Step 1: Test extracting raw values from Eloquent models
 *
 * This focuses ONLY on:
 * - if (isset($model))
 * - $value = Arr::get($model, $this->name);
 * - if (isset($value)) { $this->value = $value; }
 */
class FieldFillValueStep1Test extends BaseFieldTestCase
{
    /**
     * Test: Model is null - fillValue should not set any value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_null_model()
    {
        $field = Text::make('name');
        $field->get(null);

        // When model is null, $value property remains uninitialized
        try {
            $value = $this->getPropertyValue($field, 'value');
            $this->fail('Expected uninitialized property exception');
        } catch (\Error $e) {
            $this->assertStringContainsString('must not be accessed before initialization', $e->getMessage());
        }
    }

    /**
     * Test: Model has the field - value should be extracted and set
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_string_from_model()
    {
        $model = new MockEloquentModel(['name' => 'John Doe']);

        $field = Text::make('name');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John Doe', $value);
    }

        /**
     * Test: Field doesn't exist in model - fillValue should not set any value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_missing_field_in_model()
    {
        $model = new MockEloquentModel(['firstName' => 'John']);

        $field = Text::make('lastName'); // This field doesn't exist in model
        $field->get($model);

        // When field is missing from model, $value property remains uninitialized
        try {
            $value = $this->getPropertyValue($field, 'value');
            $this->fail('Expected uninitialized property exception');
        } catch (\Error $e) {
            $this->assertStringContainsString('must not be accessed before initialization', $e->getMessage());
        }
    }

    /**
     * Test: Model has numeric value - should be extracted as-is
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_numeric_value()
    {
        $model = new MockEloquentModel(['age' => 30]);

        $field = Text::make('age');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals(30, $value);
        $this->assertIsInt($value);
    }

    /**
     * Test: Model has zero - should be extracted (not treated as null/false)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_zero_value()
    {
        $model = new MockEloquentModel(['count' => 0]);

        $field = Text::make('count');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertSame(0, $value);
    }

    /**
     * Test: Model has false - should be extracted (not treated as null)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_false_value()
    {
        $model = new MockEloquentModel(['active' => false]);

        $field = Text::make('active');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertFalse($value);
    }

    /**
     * Test: Model has empty string - should be extracted
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_empty_string()
    {
        $model = new MockEloquentModel(['description' => '']);

        $field = Text::make('description');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('', $value);
    }

    /**
     * Test: Model has array value - should be extracted
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_array_value()
    {
        $tags = ['tag1', 'tag2', 'tag3'];
        $model = new MockEloquentModel(['tags' => $tags]);

        $field = Select::make('tags');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals($tags, $value);
        $this->assertIsArray($value);
    }

    /**
     * Test: Model has nested field using dot notation
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_nested_field_with_dot_notation()
    {
        $model = new MockEloquentModel([
            'user' => ['email' => 'john@example.com']
        ]);

        $field = Text::make('user.email');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('john@example.com', $value);
    }

    /**
     * Test: Model has object value - should be extracted as-is
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_object_value()
    {
        $metadata = (object) ['id' => 1, 'name' => 'Item'];
        $model = new MockEloquentModel(['metadata' => $metadata]);

        $field = Text::make('metadata');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertIsObject($value);
        $this->assertEquals(1, $value->id);
    }

    /**
     * Test: Multiple fields from same model - each extracts its value independently
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_multiple_fields_from_same_model()
    {
        $model = new MockEloquentModel([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30
        ]);

        $nameField = Text::make('name');
        $emailField = Text::make('email');
        $ageField = Text::make('age');

        $nameField->get($model);
        $emailField->get($model);
        $ageField->get($model);

        $this->assertEquals('John', $this->getPropertyValue($nameField, 'value'));
        $this->assertEquals('john@example.com', $this->getPropertyValue($emailField, 'value'));
        $this->assertEquals(30, $this->getPropertyValue($ageField, 'value'));
    }

    /**
     * Test: Field value previously set is overwritten by model value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_overwrites_previous_value()
    {
        $field = Text::make('name')->value('Default Name');

        // Initially has default value
        $this->assertEquals('Default Name', $this->getPropertyValue($field, 'value'));

        // After get() with model, should be overwritten
        $model = new MockEloquentModel(['name' => 'John']);
        $field->get($model);

        $this->assertEquals('John', $this->getPropertyValue($field, 'value'));
    }

    /**
     * Test: Value from model is extracted correctly when field name differs from model attribute
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_uses_field_name_property()
    {
        $model = new MockEloquentModel(['first_name' => 'John']);

        $field = Text::make('first_name');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John', $value);
    }

    /**
     * Test: fillValue is called as part of get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_is_called_during_get()
    {
        $model = new MockEloquentModel(['name' => 'Test']);

        $field = Text::make('name');
        $result = $field->get($model);

        // The result should be a serialized object with 'value' property set
        $this->assertObjectHasProperty('value', $result);
        $this->assertEquals('Test', $result->value);
    }

    /**
     * Test: Complex nested model structure with Arr::get dot notation
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_deep_nested_dot_notation()
    {
        $model = new MockEloquentModel([
            'company' => [
                'address' => [
                    'city' => 'New York'
                ]
            ]
        ]);

        $field = Text::make('company.address.city');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('New York', $value);
    }

    /**
     * Test: Null value in model - isset() should prevent setting
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_explicit_null_in_model()
    {
        $model = new MockEloquentModel(['name' => null]);

        $field = Text::make('name');
        $field->get($model);

        // When value is explicitly null, $value property remains uninitialized
        try {
            $value = $this->getPropertyValue($field, 'value');
            $this->fail('Expected uninitialized property exception');
        } catch (\Error $e) {
            $this->assertStringContainsString('must not be accessed before initialization', $e->getMessage());
        }
    }

    /**
     * Test: Boolean true value - should be extracted
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_boolean_true()
    {
        $model = new MockEloquentModel(['is_active' => true]);

        $field = Text::make('is_active');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertTrue($value);
    }

    /**
     * Test: Float value - should be extracted
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_float_value()
    {
        $model = new MockEloquentModel(['price' => 19.99]);

        $field = Text::make('price');
        $field->get($model);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals(19.99, $value);
        $this->assertIsFloat($value);
    }
}

/**
 * Mock Eloquent Model for testing
 * Implements ArrayAccess to behave like a real Eloquent model
 */
class MockEloquentModel implements \ArrayAccess
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
}
