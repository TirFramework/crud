<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Illuminate\Support\Arr;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Fields\Text;

class FieldFillValueTest extends BaseFieldTestCase
{
    /**
     * Helper to get the value property after fillValue is called
     */
    protected function getValueAfterFill($field, $model)
    {
        $field->get($model);
        try {
            return $this->getPropertyValue($field, 'value');
        } catch (\Error $e) {
            // Property not initialized (likely null)
            if (str_contains($e->getMessage(), 'must not be accessed before initialization')) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Test fillValue with no model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_null_model()
    {
        $field = Text::make('name')
            ->value('default');

        $value = $this->getValueAfterFill($field, null);

        // Value should remain as set, not extracted from null model
        $this->assertEquals('default', $value);
    }

    /**
     * Test fillValue extracts simple field from model
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_extracts_simple_field()
    {
        $model = (object) ['name' => 'John Doe'];

        $field = Text::make('name');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('John Doe', $value);
    }
    */

    /**
     * Test fillValue with dot notation (nested field)
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_dot_notation()
    {
        $model = (object) [
            'user' => (object) ['email' => 'john@example.com']
        ];

        $field = Text::make('user.email');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('john@example.com', $value);
    }
    */

    /**
     * Test fillValue with missing field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_missing_field()
    {
        $model = (object) ['name' => 'John'];

        $field = Text::make('email');
        $value = $this->getValueAfterFill($field, $model);

        // Should not have value set if field doesn't exist in model
        $this->assertNull($value);
    }

    /**
     * Test fillValue with numeric value
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_numeric_value()
    {
        $model = (object) ['age' => 30];

        $field = Text::make('age');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals(30, $value);
    }
    */

    /**
     * Test fillValue with zero value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_zero()
    {
        $model = (object) ['count' => 0];

        $field = Text::make('count');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals(0, $value);
    }

    /**
     * Test fillValue with false value
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_false()
    {
        $model = (object) ['active' => false];

        $field = Text::make('active');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertFalse($value);
    }
    */

    /**
     * Test fillValue with empty string
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_empty_string()
    {
        $model = (object) ['description' => ''];

        $field = Text::make('description');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('', $value);
    }

    /**
     * Test fillValue with array value
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_array()
    {
        $model = (object) ['tags' => ['tag1', 'tag2', 'tag3']];

        $field = Select::make('tags')
            ->multiple();

        $value = $this->getValueAfterFill($field, $model);

        $this->assertIsArray($value);
        $this->assertCount(3, $value);
    }
    */

    /**
     * Test fillValue applies accessor to extracted value
     * COMMENTED OUT: Redundant with Step 3 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_applies_accessor()
    {
        $model = (object) ['name' => 'john doe'];

        $field = Text::make('name')
            ->accessor(fn($value) => strtoupper($value));

        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('JOHN DOE', $value);
    }
    */

    /**
     * Test fillValue accessor receives model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_accessor_receives_model()
    {
        $model = (object) [
            'first_name' => 'John',
            'last_name' => 'Doe'
        ];

        $field = Text::make('full_name')
            ->virtual()
            ->accessor(fn($value, $model) => $model->first_name . ' ' . $model->last_name);

        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('John Doe', $value);
    }

    /**
     * Test fillValue accessor with null value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_accessor_with_null()
    {
        $model = (object) ['middle_name' => null];

        $field = Text::make('middle_name')
            ->accessor(fn($value) => $value ?? 'N/A');

        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('N/A', $value);
    }

    /**
     * Test fillValue with computed field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_computed_field()
    {
        $model = (object) [
            'price' => 100,
            'quantity' => 3,
            'tax_rate' => 0.1
        ];

        $field = Text::make('total')
            ->virtual()
            ->appends('price', 'quantity', 'tax_rate')
            ->accessor(fn($value, $model) => $model->price * $model->quantity * (1 + $model->tax_rate));

        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals(330, $value); // 100 * 3 * 1.1
    }

    /**
     * Test fillValue skips extraction for virtual fields
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_virtual_field_uses_accessor()
    {
        $model = (object) ['name' => 'ignored'];

        $field = Text::make('display_name')
            ->virtual()
            ->accessor(fn($value, $model) => strtoupper($model->name));

        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('IGNORED', $value);
    }

    /**
     * Test fillValue handles missing nested field gracefully
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_missing_nested_field()
    {
        $model = (object) ['user' => (object) ['name' => 'John']];

        $field = Text::make('user.email');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertNull($value);
    }

    /**
     * Test fillValue with array access notation
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_array_model()
    {
        $model = (object) ['settings' => ['theme' => 'dark', 'language' => 'en']];

        $field = Text::make('settings.theme');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals('dark', $value);
    }
    */

    /**
     * Test fillValue idempotency
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_idempotent()
    {
        $model = (object) ['name' => 'John'];

        $field = Text::make('name');

        $value1 = $this->getValueAfterFill($field, $model);
        $value2 = $this->getValueAfterFill($field, $model);

        $this->assertEquals($value1, $value2);
    }

    /**
     * Test fillValue with multiple accessor calls
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_accessor_called_on_each_get()
    {
        $callCount = 0;

        $field = Text::make('name')
            ->accessor(function($value) use (&$callCount) {
                $callCount++;
                return strtoupper($value);
            });

        $model = (object) ['name' => 'john'];
        $field->get($model);
        $field->get($model);

        // Accessor should be called each time get() is called
        $this->assertEquals(2, $callCount);
    }

    /**
     * Test fillValue with complex accessor logic
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_complex_accessor()
    {
        $model = (object) [
            'status' => 'active',
            'is_premium' => true,
            'balance' => 1000
        ];

        $field = Text::make('effective_balance')
            ->virtual()
            ->appends('balance', 'is_premium', 'status')
            ->accessor(function($value, $model) {
                if ($model->status !== 'active') return 0;
                return $model->is_premium ? $model->balance * 1.5 : $model->balance;
            });

        $value = $this->getValueAfterFill($field, $model);

        $this->assertEquals(1500, $value);
    }

    /**
     * Test fillValue with conditional accessor
     * COMMENTED OUT: Redundant with Step 3 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_conditional_accessor()
    {
        $model = (object) ['email' => 'john@example.com'];

        $field = Text::make('email')
            ->accessor(fn($value) => $value && str_contains($value, '@') ? $value : 'invalid');

        $value = $this->getValueAfterFill($field, $model);

        $this->assertStringContainsString('@', $value);
    }
    */

    /**
     * Test fillValue preserves field state
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_preserves_field_configuration()
    {
        $field = Text::make('email')
            ->display('Email Address')
            ->readonly();

        $model = (object) ['email' => 'john@example.com'];
        $fieldData = (array) $field->get($model);

        $this->assertEquals('Email Address', $fieldData['display']);
        $this->assertTrue($fieldData['readonly']);
    }

    /**
     * Test fillValue with default value not overridden
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_default_not_overridden_by_null()
    {
        $model = (object) [];

        $field = Text::make('name')
            ->default('Unknown');

        $fieldData = (array) $field->get($model);

        // fillValue only sets if field exists in model
        $this->assertNull($fieldData['value'] ?? null);
        // But default should be available
        $this->assertEquals('Unknown', $fieldData['defaultValue']);
    }

    /**
     * Test fillValue with object type field
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_with_object_field()
    {
        $data = (object) ['id' => 1, 'name' => 'Item'];
        $model = (object) ['metadata' => $data];

        $field = Text::make('metadata');
        $value = $this->getValueAfterFill($field, $model);

        $this->assertIsObject($value);
        $this->assertEquals(1, $value->id);
    }
    */

    /**
     * Test fillValue multiple fields independently
     * COMMENTED OUT: Redundant with Step 1 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_multiple_fields()
    {
        $model = (object) [
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30
        ];

        $nameField = Text::make('name');
        $emailField = Text::make('email');
        $ageField = Text::make('age');

        $this->assertEquals('John', $this->getValueAfterFill($nameField, $model));
        $this->assertEquals('john@example.com', $this->getValueAfterFill($emailField, $model));
        $this->assertEquals(30, $this->getValueAfterFill($ageField, $model));
    }
    */

    /**
     * Test fillValue with accessor modifying type
     * COMMENTED OUT: Redundant with Step 3 tests
     */
    /*
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_accessor_changes_type()
    {
        $model = (object) ['amount' => '100.50'];

        $field = Text::make('amount')
            ->accessor(fn($value) => (float) $value);

        $value = $this->getValueAfterFill($field, $model);

        $this->assertIsFloat($value);
        $this->assertEquals(100.50, $value);
    }
    */
}
