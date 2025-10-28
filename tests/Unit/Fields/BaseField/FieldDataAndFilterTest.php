<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Fields\Text;

class FieldDataAndFilterTest extends BaseFieldTestCase
{
    /**
     * Test field has empty data by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_has_empty_data_by_default()
    {
        $field = Select::make('status');

        $this->assertEmpty($this->getPropertyValue($field, 'data'));
        $this->assertEmpty($this->getPropertyValue($field, 'dataSet'));
    }

    /**
     * Test field has empty options by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_has_empty_options_by_default()
    {
        $field = Select::make('status');

        $this->assertEmpty($this->getPropertyValue($field, 'options'));
    }

    /**
     * Test data() method with array of items
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_method_with_array()
    {
        $data = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
            ['value' => 'pending', 'label' => 'Pending'],
        ];

        $field = Select::make('status')
            ->data($data);

        $fieldData = $this->getPropertyValue($field, 'data');
        $this->assertCount(3, $fieldData);
    }

    /**
     * Test data() populates dataSet
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_populates_dataset()
    {
        $data = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ];

        $field = Select::make('status')
            ->data($data);

        $dataSet = $this->getPropertyValue($field, 'dataSet');
        $this->assertNotEmpty($dataSet);
        $this->assertEquals('Active', $dataSet['active']);
        $this->assertEquals('Inactive', $dataSet['inactive']);
    }

    /**
     * Test data() with variadic parameters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_with_variadic_parameters()
    {
        $data1 = ['value' => 'active', 'label' => 'Active'];
        $data2 = ['value' => 'inactive', 'label' => 'Inactive'];

        $field = Select::make('status')
            ->data($data1, $data2);

        $fieldData = $this->getPropertyValue($field, 'data');
        $this->assertCount(2, $fieldData);
    }

    /**
     * Test options() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_options_method()
    {
        $field = Select::make('theme')
            ->options([
                'dark' => 'Dark Theme',
                'light' => 'Light Theme',
            ]);

        $options = $this->getPropertyValue($field, 'options');
        $this->assertCount(2, $options);
        $this->assertEquals('Dark Theme', $options['dark']);
        $this->assertEquals('Light Theme', $options['light']);
    }

    /**
     * Test multiple options() calls merge
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_multiple_options_calls_merge()
    {
        $field = Select::make('permissions')
            ->options(['read' => 'Read'])
            ->options(['write' => 'Write'])
            ->options(['delete' => 'Delete']);

        $options = $this->getPropertyValue($field, 'options');
        $this->assertCount(3, $options);
        $this->assertEquals('Read', $options['read']);
        $this->assertEquals('Write', $options['write']);
        $this->assertEquals('Delete', $options['delete']);
    }

    /**
     * Test filter() method enables filterable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_enables_filterable()
    {
        $field = Select::make('category')
            ->filter(['item1', 'item2']);

        $this->assertTrue($this->getPropertyValue($field, 'filterable'));
    }

    /**
     * Test filter() with items
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_with_items()
    {
        $filterItems = [
            ['value' => 'urgent', 'label' => 'Urgent'],
            ['value' => 'normal', 'label' => 'Normal'],
        ];

        $field = Select::make('priority')
            ->filter($filterItems);

        $filter = $this->getPropertyValue($field, 'filter');
        $this->assertCount(2, $filter);
        $this->assertTrue($this->getPropertyValue($field, 'filterable'));
    }

    /**
     * Test filter() without items uses data()
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_without_items_uses_data()
    {
        $data = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ];

        $field = Select::make('status')
            ->data($data)
            ->filter();

        $filter = $this->getPropertyValue($field, 'filter');
        $this->assertCount(2, $filter);
    }

    /**
     * Test filterType() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_type_method()
    {
        $field = Select::make('status')
            ->filterType('Select');

        $filterType = $this->getPropertyValue($field, 'filterType');
        $this->assertEquals('Select', $filterType);
    }

    /**
     * Test filterType() with enum
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_type_with_enum()
    {
        $field = Select::make('search')
            ->filterType(\Tir\Crud\Support\Enums\FilterType::Search);

        $filterType = $this->getPropertyValue($field, 'filterType');
        $this->assertEquals(\Tir\Crud\Support\Enums\FilterType::Search, $filterType);
    }

    /**
     * Test filterQuery() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_query_method()
    {
        $callback = function ($query) {
            return $query->where('status', 'active');
        };

        $field = Select::make('user')
            ->filterQuery($callback);

        $filterQuery = $this->getPropertyValue($field, 'filterQuery');
        $this->assertTrue(is_callable($filterQuery));
        $this->assertTrue($this->getPropertyValue($field, 'filterable'));
    }

    /**
     * Test searchQuery() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_query_method()
    {
        $callback = function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%");
        };

        $field = Select::make('user')
            ->searchQuery($callback);

        $searchQuery = $this->getPropertyValue($field, 'searchQuery');
        $this->assertTrue(is_callable($searchQuery));
        $this->assertTrue($this->getPropertyValue($field, 'searchable'));
    }

    /**
     * Test typical select field setup
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_typical_select_field()
    {
        $field = Select::make('status')
            ->display('Status')
            ->data([
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ])
            ->default('active');

        $data = $this->getPropertyValue($field, 'data');
        $display = $this->getPropertyValue($field, 'display');
        $default = $this->getPropertyValue($field, 'defaultValue');

        $this->assertCount(2, $data);
        $this->assertEquals('Status', $display);
        $this->assertEquals('active', $default);
    }

    /**
     * Test data with complex structure
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_with_complex_structure()
    {
        $data = [
            ['value' => 'us', 'label' => 'United States', 'region' => 'Americas'],
            ['value' => 'uk', 'label' => 'United Kingdom', 'region' => 'Europe'],
            ['value' => 'au', 'label' => 'Australia', 'region' => 'Oceania'],
        ];

        $field = Select::make('country')
            ->data($data);

        $fieldData = $this->getPropertyValue($field, 'data');
        $this->assertCount(3, $fieldData);
        $this->assertEquals('United States', $fieldData[0]['label']);
    }

    /**
     * Test data available via get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_available_in_get_method()
    {
        $data = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ];

        $field = Select::make('status')
            ->data($data);

        $fieldData = $field->get(null);
        $this->assertNotEmpty($fieldData->data);
        $this->assertCount(2, $fieldData->data);
    }

    /**
     * Test options available via get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_options_available_in_get_method()
    {
        $field = Select::make('theme')
            ->options(['dark' => 'Dark', 'light' => 'Light']);

        $fieldData = $field->get(null);
        $this->assertNotEmpty($fieldData->options);
        $this->assertEquals('Dark', $fieldData->options['dark']);
    }

    /**
     * Test filter available via get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_available_in_get_method()
    {
        $field = Select::make('status')
            ->filter(['active', 'inactive']);

        $fieldData = $field->get(null);
        $this->assertTrue($fieldData->filterable);
        $this->assertNotEmpty($fieldData->filter);
    }

    /**
     * Test dataSet is populated correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_dataset_population()
    {
        $data = [
            ['value' => 'admin', 'label' => 'Administrator'],
            ['value' => 'user', 'label' => 'Regular User'],
            ['value' => 'guest', 'label' => 'Guest'],
        ];

        $field = Select::make('role')
            ->data($data);

        $dataSet = $this->getPropertyValue($field, 'dataSet');
        $this->assertCount(3, $dataSet);
        $this->assertEquals('Administrator', $dataSet['admin']);
        $this->assertEquals('Regular User', $dataSet['user']);
        $this->assertEquals('Guest', $dataSet['guest']);
    }

    /**
     * Test method chaining with data methods
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_method_chaining()
    {
        $field = Select::make('priority')
            ->display('Priority Level')
            ->data([
                ['value' => 'high', 'label' => 'High'],
                ['value' => 'medium', 'label' => 'Medium'],
                ['value' => 'low', 'label' => 'Low'],
            ])
            ->filter()
            ->filterType('Select')
            ->default('medium')
            ->sortable();

        $display = $this->getPropertyValue($field, 'display');
        $filterable = $this->getPropertyValue($field, 'filterable');
        $sortable = $this->getPropertyValue($field, 'sortable');

        $this->assertEquals('Priority Level', $display);
        $this->assertTrue($filterable);
        $this->assertTrue($sortable);
    }

    /**
     * Test empty data scenario
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_empty_data_field()
    {
        $field = Select::make('category');

        $data = $this->getPropertyValue($field, 'data');
        $this->assertEmpty($data);
    }

    /**
     * Test combining data with options
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_combining_data_and_options()
    {
        $field = Select::make('preferences')
            ->data([
                ['value' => 'email', 'label' => 'Email'],
                ['value' => 'sms', 'label' => 'SMS'],
            ])
            ->options(['placeholder' => 'Choose notification method']);

        $data = $this->getPropertyValue($field, 'data');
        $options = $this->getPropertyValue($field, 'options');

        $this->assertCount(2, $data);
        $this->assertCount(1, $options);
    }

    /**
     * Test filter with multiple data types
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_with_string_values()
    {
        $field = Select::make('status')
            ->filter('active', 'inactive', 'pending');

        $filter = $this->getPropertyValue($field, 'filter');
        $this->assertCount(3, $filter);
    }

    /**
     * Test data with numeric values
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_with_numeric_values()
    {
        $data = [
            ['value' => 1, 'label' => 'First'],
            ['value' => 2, 'label' => 'Second'],
            ['value' => 3, 'label' => 'Third'],
        ];

        $field = Select::make('position')
            ->data($data);

        $fieldData = $this->getPropertyValue($field, 'data');
        $this->assertCount(3, $fieldData);
        $this->assertEquals(1, $fieldData[0]['value']);
    }

    /**
     * Test filter method sets filterable to true when called with no arguments and no data
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_method_sets_filterable_when_no_arguments_and_no_data()
    {
        $field = Select::make('status');

        // Call filter() with no arguments and chain another method
        $result = $field->filter()->display('Status Field');

        // Should return the same instance for method chaining
        $this->assertSame($field, $result);

        // Should set filterable to true
        $this->assertTrue($this->getPropertyValue($field, 'filterable'));

        // Filter should remain empty since no data and no arguments
        $this->assertEmpty($this->getPropertyValue($field, 'filter'));

        // Verify chaining worked by checking the display was set
        $this->assertEquals('Status Field', $this->getPropertyValue($field, 'display'));
    }

    /**
     * Test filter method supports method chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_method_supports_chaining()
    {
        $field = Select::make('status')
            ->filter()
            ->display('Status Field')
            ->class('form-control');

        $this->assertTrue($this->getPropertyValue($field, 'filterable'));
        $this->assertEquals('Status Field', $this->getPropertyValue($field, 'display'));
        $this->assertEquals(' form-control', $this->getPropertyValue($field, 'class'));
    }
}
