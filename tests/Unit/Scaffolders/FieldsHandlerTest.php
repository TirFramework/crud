<?php

namespace Tir\Crud\Tests\Unit\Scaffolders;

use Tir\Crud\Tests\TestCase;
use Tir\Crud\Support\Scaffold\FieldsHandler;

/**
 * Test FieldsHandler functionality
 */
class FieldsHandlerTest extends TestCase
{
    /**
     * Create a mock field object that mimics field->get(model) output
     */
    private function createMockField(
        string $name,
        bool $showOnIndex = true,
        bool $showOnCreating = true,
        bool $showOnEditing = true,
        bool $showOnDetail = true,
        bool $searchable = false,
        bool $virtual = false,
        bool $fillable = true,
        ?array $children = null,
        bool $shouldGetChildren = true
    ): object {
        $field = new \stdClass();
        $field->name = $name;
        $field->showOnIndex = $showOnIndex;
        $field->showOnCreating = $showOnCreating;
        $field->showOnEditing = $showOnEditing;
        $field->showOnDetail = $showOnDetail;
        $field->searchable = $searchable;
        $field->virtual = $virtual;
        $field->fillable = $fillable;

        if ($children !== null) {
            $field->children = $children;
            $field->shouldGetChildren = $shouldGetChildren;
        }

        return $field;
    }

    /**
     * Create a mock field object that mimics BaseField before ->get(model) call
     */
    private function createMockBaseField(string $name): object
    {
        $field = $this->createMock(\Tir\Crud\Support\Scaffold\Fields\BaseField::class);
        $field->method('page')->willReturnSelf();
        $field->method('readonly')->willReturnSelf();
        $field->method('get')->willReturn($this->createMockField($name));
        return $field;
    }

    /**
     * Test that constructor processes fields correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_constructor_processes_fields_correctly()
    {
        $mockField1 = $this->createMockBaseField('name');
        $mockField2 = $this->createMockBaseField('email');

        $handler = new FieldsHandler([$mockField1, $mockField2], 'create');

        $fields = $handler->getFields();
        $this->assertCount(2, $fields);
        $this->assertEquals('name', $fields[0]->name);
        $this->assertEquals('email', $fields[1]->name);
    }

    /**
     * Test that detail page sets fields to readonly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_detail_page_sets_fields_to_readonly()
    {
        $mockField = $this->createMock(\Tir\Crud\Support\Scaffold\Fields\BaseField::class);
        $mockField->expects($this->once())->method('page')->with('detail')->willReturnSelf();
        $mockField->expects($this->once())->method('readonly')->willReturnSelf();
        $mockField->method('get')->willReturn($this->createMockField('name'));

        $handler = new FieldsHandler([$mockField], 'detail');

        $this->assertNotNull($handler->getFields());
    }

    /**
     * Test that getIndexFields returns only fields shown on index
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_index_fields_returns_only_fields_shown_on_index()
    {
        $field1 = $this->createMockField('name', true);  // showOnIndex = true
        $field2 = $this->createMockField('email', false); // showOnIndex = false
        $field3 = $this->createMockField('status', true); // showOnIndex = true

        // Create handler with pre-processed fields (simulating constructor output)
        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3]);

        $indexFields = $instance->getIndexFields();

        $this->assertCount(2, $indexFields);
        $this->assertEquals('name', $indexFields[0]->name);
        $this->assertEquals('status', $indexFields[1]->name);
    }

    /**
     * Test that getCreateFields returns only fields shown on creating
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_create_fields_returns_only_fields_shown_on_creating()
    {
        $field1 = $this->createMockField('name', showOnCreating: true);
        $field2 = $this->createMockField('email', showOnCreating: false);
        $field3 = $this->createMockField('status', showOnCreating: true);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3]);

        $createFields = $instance->getCreateFields();

        $this->assertCount(2, $createFields);
        $this->assertEquals('name', $createFields[0]->name);
        $this->assertEquals('status', $createFields[1]->name);
    }

    /**
     * Test that getEditFields returns only fields shown on editing
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_edit_fields_returns_only_fields_shown_on_editing()
    {
        $field1 = $this->createMockField('name', showOnEditing: true);
        $field2 = $this->createMockField('email', showOnEditing: false);
        $field3 = $this->createMockField('status', showOnEditing: true);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3]);

        $editFields = $instance->getEditFields();

        $this->assertCount(2, $editFields);
        $this->assertEquals('name', $editFields[0]->name);
        $this->assertEquals('status', $editFields[1]->name);
    }

    /**
     * Test that getDetailFields returns only fields shown on detail
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_detail_fields_returns_only_fields_shown_on_detail()
    {
        $field1 = $this->createMockField('name', showOnDetail: true);
        $field2 = $this->createMockField('email', showOnDetail: false);
        $field3 = $this->createMockField('status', showOnDetail: true);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3]);

        $detailFields = $instance->getDetailFields();

        $this->assertCount(2, $detailFields);
        $this->assertEquals('name', $detailFields[0]->name);
        $this->assertEquals('status', $detailFields[1]->name);
    }

    /**
     * Test that fields are cached after first access
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fields_are_cached_after_first_access()
    {
        $field1 = $this->createMockField('name', true);
        $field2 = $this->createMockField('email', true);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2]);

        // First call
        $indexFields1 = $instance->getIndexFields();
        $this->assertCount(2, $indexFields1);

        // Second call should return cached result
        $indexFields2 = $instance->getIndexFields();
        $this->assertCount(2, $indexFields2);
        $this->assertSame($indexFields1, $indexFields2); // Same reference = cached
    }

    /**
     * Test that getAllFields returns flattened field structure
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_all_fields_returns_flattened_field_structure()
    {
        $child1 = $this->createMockField('child1');
        $child2 = $this->createMockField('child2');
        $parent = $this->createMockField('parent', children: [$child1, $child2]);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$parent]);

        $allFields = $instance->getAllFields();

        $this->assertCount(3, $allFields);
        $this->assertEquals('parent', $allFields[0]->name);
        $this->assertEquals('child1', $allFields[1]->name);
        $this->assertEquals('child2', $allFields[2]->name);
    }

    /**
     * Test that getAllDataFields returns only fillable non-virtual fields
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_all_data_fields_returns_only_fillable_non_virtual_fields()
    {
        $field1 = $this->createMockField('name', virtual: false, fillable: true);
        $field2 = $this->createMockField('email', virtual: true, fillable: true);  // virtual
        $field3 = $this->createMockField('status', virtual: false, fillable: false); // not fillable
        $field4 = $this->createMockField('computed', virtual: false, fillable: true);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3, $field4]);

        $dataFields = $instance->getAllDataFields();

        $this->assertCount(2, $dataFields);
        $this->assertEquals('name', $dataFields[0]->name);
        $this->assertEquals('computed', $dataFields[1]->name);
    }

    /**
     * Test that getFieldByName finds field by name
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_field_by_name_finds_field_by_name()
    {
        $field1 = $this->createMockField('name');
        $field2 = $this->createMockField('email');
        $field3 = $this->createMockField('status');

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3]);

        $foundField = $instance->getFieldByName('email');

        $this->assertNotNull($foundField);
        $this->assertEquals('email', $foundField->name);
    }

    /**
     * Test that getFieldByName returns null for non-existent field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_field_by_name_returns_null_for_non_existent_field()
    {
        $field1 = $this->createMockField('name');
        $field2 = $this->createMockField('email');

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2]);

        $foundField = $instance->getFieldByName('nonexistent');

        $this->assertNull($foundField);
    }

    /**
     * Test that getSearchableFields returns only searchable fields from index
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_searchable_fields_returns_only_searchable_fields_from_index()
    {
        $field1 = $this->createMockField('name', true, searchable: true);
        $field2 = $this->createMockField('email', true, searchable: false);
        $field3 = $this->createMockField('status', false, searchable: true); // not on index
        $field4 = $this->createMockField('description', true, searchable: true);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$field1, $field2, $field3, $field4]);

        $searchableFields = $instance->getSearchableFields();

        $this->assertCount(2, $searchableFields);
        $this->assertEquals('name', $searchableFields[0]->name);
        $this->assertEquals('description', $searchableFields[1]->name);
    }

    /**
     * Test that children are filtered correctly in getIndexFields method (flattened)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_children_are_filtered_correctly_in_get_index_fields_method()
    {
        $child1 = $this->createMockField('child1', showOnIndex: true);
        $child2 = $this->createMockField('child2', showOnIndex: false);
        $parent = $this->createMockField('parent', showOnIndex: true, children: [$child1, $child2]);

        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, [$parent]);

        $indexFields = $instance->getIndexFields();

        // getIndexFields flattens the hierarchy, so we get parent + visible children
        $this->assertCount(2, $indexFields);
        $this->assertEquals('parent', $indexFields[0]->name);
        $this->assertEquals('child1', $indexFields[1]->name);
    }    /**
     * Test that empty fields array is handled correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_empty_fields_array_is_handled_correctly()
    {
        $handler = new \ReflectionClass(FieldsHandler::class);
        $instance = $handler->newInstanceWithoutConstructor();
        $reflectionProperty = $handler->getProperty('fields');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($instance, []);

        $this->assertEmpty($instance->getFields());
        $this->assertEmpty($instance->getIndexFields());
        $this->assertEmpty($instance->getCreateFields());
        $this->assertEmpty($instance->getEditFields());
        $this->assertEmpty($instance->getDetailFields());
        $this->assertEmpty($instance->getAllFields());
        $this->assertEmpty($instance->getAllDataFields());
        $this->assertEmpty($instance->getSearchableFields());
    }
}
