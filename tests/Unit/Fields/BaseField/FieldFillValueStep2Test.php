<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Tests\Unit\Fields\BaseField\BaseFieldTestCase;

class FieldFillValueStep2Test extends BaseFieldTestCase
{
    /**
     * Test: No relation configured - setRelationalValue should not be called
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step2_no_relation_configured()
    {
        $model = new MockEloquentModelStep2(['name' => 'John']);

        $field = Text::make('name');
        $field->get($model);

        // Should have extracted the raw value from Step 1
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John', $value);
    }

    /**
     * Test: Relation configured but setRelationalValue returns empty array - should not set value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step2_relation_returns_empty_array()
    {
        $model = new MockEloquentModelStep2(['name' => 'John']);
        // Set categories to empty collection
        $model->categories = collect([]);

        $field = Text::make('name');
        // Configure a relation that will return empty array
        $field->relation('categories', 'name');

        $field->get($model);

        // Should still have the raw value from Step 1 (relation didn't override)
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('John', $value);
    }

    /**
     * Test: Relation configured and returns values - should set relational value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step2_relation_returns_values()
    {
        // Create a model with categories relation
        $category1 = new MockEloquentModelStep2(['id' => 1, 'name' => 'Tech']);
        $category2 = new MockEloquentModelStep2(['id' => 2, 'name' => 'News']);

        $model = new MockEloquentModelStep2(['title' => 'Test Post']);
        // Mock the categories relation to return our test categories
        $model->categories = collect([$category1, $category2]);

        $field = Text::make('categories');
        $field->relation('categories', 'name'); // Display category names

        $field->get($model);

        // Should have relational values, not the raw value
        try {
            $value = $this->getPropertyValue($field, 'value');
            $this->assertEquals(['Tech', 'News'], $value);
        } catch (\Error $e) {
            $this->fail('Value should have been set by relation: ' . $e->getMessage());
        }
    }

    /**
     * Test: Relation configured and returns single value - should set as array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step2_relation_returns_single_value()
    {
        // Create a model with author relation
        $author = new MockEloquentModelStep2(['id' => 1, 'name' => 'John Doe']);

        $model = new MockEloquentModelStep2(['title' => 'Test Post']);
        // Mock the author relation
        $model->author = $author;

        $field = Text::make('author');
        $field->relation('author', 'name')->multiple(); // Display author name

        $field->get($model);

        // Should have relational value as array
        try {
            $value = $this->getPropertyValue($field, 'value');
            $this->assertEquals('John Doe', $value);
        } catch (\Error $e) {
            $this->fail('Value should have been set by relation: ' . $e->getMessage());
        }
    }

    /**
     * Test: Relation overrides raw value from Step 1
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step2_relation_overrides_raw_value()
    {
        // Create a model with both raw field and relation
        $category = new MockEloquentModelStep2(['id' => 1, 'name' => 'Tech']);

        $model = new MockEloquentModelStep2(['category_id' => 999, 'title' => 'Test Post']);
        // Mock the category relation
        $model->category = $category;

        $field = Text::make('category_id'); // Raw field has value 999
        $field->relation('category', 'name'); // But relation should override

        $field->get($model);

        // Should have relational value, not the raw field value
        $value = $this->getPropertyValue($field, 'value');
        $this->assertEquals('Tech', $value);
    }

    /**
     * Test: Relation configured but model has no such relation method - should throw exception
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fill_value_step2_invalid_relation_throws_exception()
    {
        $model = new MockEloquentModelStep2(['name' => 'John']);

        $field = Text::make('name');
        $field->relation('invalidRelation', 'name');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The Relation "invalidRelation" not found on model');

        $field->get($model);
    }
}

/**
 * Mock Eloquent Model for Step 2 testing with relation methods
 */
class MockEloquentModelStep2 extends MockEloquentModel
{
    // Mock relation methods for testing - return mock relation instances
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

/**
 * Mock BelongsToMany relation - simplified
 */
class BelongsToMany
{
    public function getRelated() { return new \stdClass(); }
}

/**
 * Mock BelongsTo relation - simplified
 */
class BelongsTo
{
    public function getRelated() { return new \stdClass(); }
}
