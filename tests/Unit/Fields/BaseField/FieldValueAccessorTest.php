<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Select;

class FieldValueAccessorTest extends BaseFieldTestCase
{
    /**
     * Test field has no accessor by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_has_no_accessor_by_default()
    {
        $field = Text::make('name');

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $this->assertNull($valueAccessor);
    }

    /**
     * Test accessor() method sets callback
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_sets_callback()
    {
        $callback = fn($value) => strtoupper($value);

        $field = Text::make('name')
            ->accessor($callback);

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $this->assertTrue(is_callable($valueAccessor));
    }

    /**
     * Test accessor() returns static for chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_returns_static()
    {
        $field = Text::make('name')
            ->accessor(fn($value) => strtoupper($value));

        $this->assertInstanceOf(Text::class, $field);
    }

    /**
     * Test simple transform accessor
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_simple_transform_accessor()
    {
        $callback = fn($value) => strtoupper($value);

        $field = Text::make('name')
            ->accessor($callback);

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor('john');

        $this->assertEquals('JOHN', $result);
    }

    /**
     * Test accessor with model context
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_with_model_context()
    {
        $callback = fn($value, $model) => strtoupper($model->title ?? $value);

        $field = Text::make('name')
            ->accessor($callback);

        $model = (object) ['title' => 'admin user'];
        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor('john', $model);

        $this->assertEquals('ADMIN USER', $result);
    }

    /**
     * Test accessor for field concatenation
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_for_field_concatenation()
    {
        $callback = fn($value, $model) => $model->first_name . ' ' . $model->last_name;

        $field = Text::make('full_name')
            ->virtual()
            ->accessor($callback);

        $model = (object) ['first_name' => 'John', 'last_name' => 'Doe'];
        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor(null, $model);

        $this->assertEquals('John Doe', $result);
    }

    /**
     * Test accessor for business logic
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_for_business_logic()
    {
        $callback = fn($value, $model) => $model->is_premium ? $value * 0.9 : $value;

        $field = Text::make('price')
            ->accessor($callback);

        $premiumModel = (object) ['is_premium' => true];
        $regularModel = (object) ['is_premium' => false];

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');

        $premiumPrice = $valueAccessor(100, $premiumModel);
        $regularPrice = $valueAccessor(100, $regularModel);

        $this->assertEquals(90, $premiumPrice);
        $this->assertEquals(100, $regularPrice);
    }

    /**
     * Test accessor for date formatting
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_for_date_formatting()
    {
        $callback = fn($value) => $value ? date('Y-m-d', strtotime($value)) : null;

        $field = Text::make('created_at')
            ->accessor($callback);

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor('2025-10-28 14:30:00');

        $this->assertEquals('2025-10-28', $result);
    }

    /**
     * Test appends() with single column
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_with_single_column()
    {
        $field = Text::make('full_name')
            ->virtual()
            ->appends('first_name');

        $appends = $this->getPropertyValue($field, 'appends');
        $this->assertCount(1, $appends);
        $this->assertContains('first_name', $appends);
    }

    /**
     * Test appends() with array of columns
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_with_array_of_columns()
    {
        $field = Text::make('full_name')
            ->virtual()
            ->appends(['first_name', 'last_name']);

        $appends = $this->getPropertyValue($field, 'appends');
        $this->assertCount(2, $appends);
        $this->assertContains('first_name', $appends);
        $this->assertContains('last_name', $appends);
    }

    /**
     * Test appends() with variadic parameters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_with_variadic_parameters()
    {
        $field = Text::make('total_price')
            ->virtual()
            ->appends('price', 'quantity', 'tax_rate');

        $appends = $this->getPropertyValue($field, 'appends');
        $this->assertCount(3, $appends);
        $this->assertContains('price', $appends);
        $this->assertContains('quantity', $appends);
        $this->assertContains('tax_rate', $appends);
    }

    /**
     * Test appends() returns static for chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_returns_static()
    {
        $field = Text::make('full_name')
            ->virtual()
            ->appends('first_name', 'last_name');

        $this->assertInstanceOf(Text::class, $field);
    }

    /**
     * Test appends() empty by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_empty_by_default()
    {
        $field = Text::make('name');

        $appends = $this->getPropertyValue($field, 'appends');
        $this->assertEmpty($appends);
    }

    /**
     * Test value() method sets value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_method_sets_value()
    {
        $field = Text::make('name')
            ->value('John Doe');

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John Doe', $value);
    }

    /**
     * Test value() with null
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_with_null()
    {
        $field = Text::make('name')
            ->value(null);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertNull($value);
    }

    /**
     * Test value() with numeric value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_with_numeric_value()
    {
        $field = Text::make('count')
            ->value(42);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals(42, $value);
    }

    /**
     * Test value() with array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_with_array()
    {
        $field = Select::make('tags')
            ->multiple()
            ->value(['tag1', 'tag2', 'tag3']);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertIsArray($value);
        $this->assertCount(3, $value);
    }

    /**
     * Test value() with object
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_with_object()
    {
        $obj = (object) ['id' => 1, 'name' => 'Test'];

        $field = Text::make('data')
            ->value($obj);

        $value = $this->getPropertyValue($field, 'value');
        $this->assertIsObject($value);
        $this->assertEquals('Test', $value->name);
    }

    /**
     * Test value() returns static for chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_returns_static()
    {
        $field = Text::make('name')
            ->value('John');

        $this->assertInstanceOf(Text::class, $field);
    }

    /**
     * Test accessor and appends together
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_and_appends_together()
    {
        $field = Text::make('full_name')
            ->virtual()
            ->appends('first_name', 'last_name')
            ->accessor(fn($value, $model) => $model->first_name . ' ' . $model->last_name);

        $accessor = $this->getPropertyValue($field, 'valueAccessor');
        $appends = $this->getPropertyValue($field, 'appends');

        $this->assertTrue(is_callable($accessor));
        $this->assertCount(2, $appends);
    }

    /**
     * Test typical virtual field setup
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_typical_virtual_field_setup()
    {
        $field = Text::make('full_name')
            ->display('Full Name')
            ->virtual()
            ->appends('first_name', 'last_name')
            ->accessor(fn($value, $model) => strtoupper($model->first_name . ' ' . $model->last_name))
            ->readonly();

        $isVirtual = $this->getPropertyValue($field, 'virtual');
        $isReadonly = $this->getPropertyValue($field, 'readonly');
        $isFillable = $this->getPropertyValue($field, 'fillable');
        $accessor = $this->getPropertyValue($field, 'valueAccessor');
        $appends = $this->getPropertyValue($field, 'appends');

        $this->assertTrue($isVirtual);
        $this->assertTrue($isReadonly);
        $this->assertFalse($isFillable);
        $this->assertTrue(is_callable($accessor));
        $this->assertCount(2, $appends);
    }

    /**
     * Test accessor available in get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_available_in_get_method()
    {
        $callback = fn($value) => strtoupper($value);

        $field = Text::make('name')
            ->accessor($callback);

        $fieldData = $field->get(null);
        $this->assertTrue(is_callable($fieldData->valueAccessor));
    }

    /**
     * Test appends available in get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_available_in_get_method()
    {
        $field = Text::make('full_name')
            ->virtual()
            ->appends('first_name', 'last_name');

        $fieldData = $field->get(null);
        $this->assertCount(2, $fieldData->appends);
    }

    /**
     * Test value available in get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_value_available_in_get_method()
    {
        $field = Text::make('name')
            ->value('John Doe');

        $fieldData = $field->get(null);
        $this->assertEquals('John Doe', $fieldData->value);
    }

    /**
     * Test accessor with multiple transformations
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_with_multiple_transformations()
    {
        $callback = fn($value) => strtoupper(trim($value ?? ''));

        $field = Text::make('name')
            ->accessor($callback);

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor('  john doe  ');

        $this->assertEquals('JOHN DOE', $result);
    }

    /**
     * Test accessor with null handling
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_with_null_handling()
    {
        $callback = fn($value) => $value ? strtoupper($value) : 'N/A';

        $field = Text::make('name')
            ->accessor($callback);

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');

        $resultWithValue = $valueAccessor('john');
        $resultWithNull = $valueAccessor(null);

        $this->assertEquals('JOHN', $resultWithValue);
        $this->assertEquals('N/A', $resultWithNull);
    }

    /**
     * Test computed field pattern
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_computed_field_pattern()
    {
        $field = Text::make('total_amount')
            ->virtual()
            ->appends('price', 'quantity', 'discount')
            ->accessor(fn($value, $model) => ($model->price * $model->quantity) - $model->discount);

        $model = (object) [
            'price' => 100,
            'quantity' => 3,
            'discount' => 50
        ];

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor(null, $model);

        $this->assertEquals(250, $result);
    }

    /**
     * Test multiple appends parameters as mixed types
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_with_mixed_call_styles()
    {
        $field = Text::make('full_name')
            ->virtual()
            ->appends(['first_name', 'middle_name'], 'last_name');

        $appends = $this->getPropertyValue($field, 'appends');
        $this->assertNotEmpty($appends);
    }

    /**
     * Test accessor replaces original value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_accessor_replaces_original_value()
    {
        $callback = fn($value, $model) => 'transformed_' . $value;

        $field = Text::make('name')
            ->value('john')
            ->accessor($callback);

        $valueAccessor = $this->getPropertyValue($field, 'valueAccessor');
        $result = $valueAccessor('john', (object)[]);

        $this->assertEquals('transformed_john', $result);
        $this->assertNotEquals('john', $result);
    }

    /**
     * Test appends is included in serialization
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_appends_in_serialization()
    {
        $field = Text::make('full_name')
            ->appends('first_name', 'last_name');

        $fieldData = $field->get(null);
        $this->assertObjectHasProperty('appends', $fieldData);
        $this->assertCount(2, $fieldData->appends);
    }
}
