<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Fields\Text;

class FieldRelationTest extends BaseFieldTestCase
{
    /**
     * Test field has no relation by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_has_no_relation_by_default()
    {
        $field = Select::make('user');

        try {
            $relation = $this->getPropertyValue($field, 'relation');
            $this->fail('Expected uninitialized property exception');
        } catch (\Error $e) {
            // Expected - relation not initialized until relation() is called
            $this->assertStringContainsString('must not be accessed before initialization', $e->getMessage());
        }
    }

    /**
     * Test relation() method sets relation properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_sets_properties()
    {
        $field = Select::make('user')
            ->relation('users', 'name');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertIsObject($relation);
        $this->assertEquals('users', $relation->name);
        $this->assertEquals('name', $relation->field);
        $this->assertEquals('id', $relation->key);
        $this->assertEquals('', $relation->type);
    }

    /**
     * Test relation() with custom primary key
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_custom_primary_key()
    {
        $field = Select::make('owner')
            ->relation('users', 'full_name', '', 'user_id');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('user_id', $relation->key);
        $this->assertEquals('users', $relation->name);
        $this->assertEquals('full_name', $relation->field);
    }

    /**
     * Test relation() with relation type
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_type()
    {
        $field = Select::make('category')
            ->relation('categories', 'title', 'hasMany');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('categories', $relation->name);
        $this->assertEquals('title', $relation->field);
        $this->assertEquals('hasMany', $relation->type);
        $this->assertEquals('id', $relation->key);
    }

    /**
     * Test relation() returns static for chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_returns_static()
    {
        $field = Select::make('user')
            ->relation('users', 'name');

        $this->assertInstanceOf(Select::class, $field);
    }

    /**
     * Test typical belongsTo relation setup
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_typical_belongs_to_relation()
    {
        $field = Select::make('user_id')
            ->display('User')
            ->relation('users', 'name')
            ->searchable();

        $relation = $this->getPropertyValue($field, 'relation');
        $searchable = $this->getPropertyValue($field, 'searchable');

        $this->assertEquals('users', $relation->name);
        $this->assertEquals('name', $relation->field);
        $this->assertTrue($searchable);
    }

    /**
     * Test relation with multiple configuration options
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_multiple_configurations()
    {
        $field = Select::make('department_id')
            ->display('Department')
            ->relation('departments', 'department_name', 'belongsTo', 'dept_id')
            ->multiple()
            ->filter(['option1', 'option2']);

        $relation = $this->getPropertyValue($field, 'relation');
        $multiple = $this->getPropertyValue($field, 'multiple');
        $filterable = $this->getPropertyValue($field, 'filterable');

        $this->assertEquals('departments', $relation->name);
        $this->assertEquals('department_name', $relation->field);
        $this->assertEquals('belongsTo', $relation->type);
        $this->assertEquals('dept_id', $relation->key);
        $this->assertTrue($multiple);
        $this->assertTrue($filterable);
    }

    /**
     * Test relation() minimum parameters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_minimum_parameters()
    {
        $field = Select::make('author')
            ->relation('users', 'name');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertNotNull($relation);
        $this->assertEquals('users', $relation->name);
        $this->assertEquals('name', $relation->field);
    }

    /**
     * Test relation with custom type and key
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_custom_type_and_key()
    {
        $field = Select::make('company')
            ->relation('companies', 'company_name', 'hasOne', 'company_id');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('companies', $relation->name);
        $this->assertEquals('company_name', $relation->field);
        $this->assertEquals('hasOne', $relation->type);
        $this->assertEquals('company_id', $relation->key);
    }

    /**
     * Test relation available in get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_available_in_get_method()
    {
        $field = Select::make('user')
            ->relation('users', 'name');

        $fieldData = $field->get(null);

        $this->assertObjectHasProperty('relation', $fieldData);
        $this->assertEquals('users', $fieldData->relation->name);
        $this->assertEquals('name', $fieldData->relation->field);
    }

    /**
     * Test relation properties in serialization
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_full_serialization()
    {
        $field = Select::make('user_id')
            ->relation('users', 'full_name', 'belongsTo', 'uuid');

        $fieldData = $field->get(null);

        $this->assertIsObject($fieldData->relation);
        $this->assertEquals('users', $fieldData->relation->name);
        $this->assertEquals('full_name', $fieldData->relation->field);
        $this->assertEquals('belongsTo', $fieldData->relation->type);
        $this->assertEquals('uuid', $fieldData->relation->key);
    }

    /**
     * Test relation with numeric field name
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_field_with_numeric_name()
    {
        $field = Select::make('category_id')
            ->relation('categories', 'name');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('name', $relation->field);
    }

    /**
     * Test relation as object type
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_is_object()
    {
        $field = Select::make('user')
            ->relation('users', 'name');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertIsObject($relation);
        $this->assertTrue(property_exists($relation, 'name'));
        $this->assertTrue(property_exists($relation, 'field'));
        $this->assertTrue(property_exists($relation, 'key'));
        $this->assertTrue(property_exists($relation, 'type'));
    }

    /**
     * Test relation with default id key
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_default_primary_key()
    {
        $field = Select::make('status')
            ->relation('statuses', 'label');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('id', $relation->key);
    }

    /**
     * Test multiple relation fields in succession
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_multiple_fields_with_relations()
    {
        $userField = Select::make('user_id')
            ->relation('users', 'name');

        $roleField = Select::make('role_id')
            ->relation('roles', 'title');

        $userRelation = $this->getPropertyValue($userField, 'relation');
        $roleRelation = $this->getPropertyValue($roleField, 'relation');

        $this->assertEquals('users', $userRelation->name);
        $this->assertEquals('roles', $roleRelation->name);
    }

    /**
     * Test relation field with hidden condition
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_field_with_visibility()
    {
        $field = Select::make('admin_id')
            ->relation('users', 'name')
            ->hideFromIndex()
            ->showOnDetail();

        $relation = $this->getPropertyValue($field, 'relation');
        $showOnIndex = $this->getPropertyValue($field, 'showOnIndex');
        $showOnDetail = $this->getPropertyValue($field, 'showOnDetail');

        $this->assertIsObject($relation);
        $this->assertFalse($showOnIndex);
        $this->assertTrue($showOnDetail);
    }

    /**
     * Test relation with data for fallback
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_data_fallback()
    {
        $data = [
            ['value' => 1, 'label' => 'Admin'],
            ['value' => 2, 'label' => 'User'],
        ];

        $field = Select::make('role_id')
            ->relation('roles', 'name')
            ->data($data);

        $relation = $this->getPropertyValue($field, 'relation');
        $fieldData = $this->getPropertyValue($field, 'data');

        $this->assertIsObject($relation);
        $this->assertCount(2, $fieldData);
    }

    /**
     * Test relation with empty type parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_empty_type()
    {
        $field = Select::make('user')
            ->relation('users', 'name', '', 'id');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('', $relation->type);
    }

    /**
     * Test relation chaining with other methods
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_chaining_pattern()
    {
        $field = Select::make('user_id')
            ->display('Select User')
            ->relation('users', 'email')
            ->placeholder('Choose a user')
            ->searchable()
            ->filter()
            ->hideFromIndex();

        $relation = $this->getPropertyValue($field, 'relation');
        $display = $this->getPropertyValue($field, 'display');
        $placeholder = $this->getPropertyValue($field, 'placeholder');

        $this->assertEquals('users', $relation->name);
        $this->assertEquals('Select User', $display);
        $this->assertEquals('Choose a user', $placeholder);
    }

    /**
     * Test required() method exists (companion to relation)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_field_can_chain_rules()
    {
        $field = Select::make('user_id')
            ->relation('users', 'name')
            ->rules('required', 'exists:users,id');

        $relation = $this->getPropertyValue($field, 'relation');
        $rules = $this->getPropertyValue($field, 'rules');

        $this->assertIsObject($relation);
        $this->assertNotEmpty($rules);
    }

    /**
     * Test relation with special characters in field name
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_underscore_names()
    {
        $field = Select::make('parent_user_id')
            ->relation('user_accounts', 'display_name');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('user_accounts', $relation->name);
        $this->assertEquals('display_name', $relation->field);
    }

    /**
     * Test relation with polymorphic type
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_with_polymorphic_type()
    {
        $field = Select::make('taggable')
            ->relation('tags', 'name', 'morphMany', 'tag_id');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertEquals('tags', $relation->name);
        $this->assertEquals('morphMany', $relation->type);
    }

    /**
     * Test relation object structure completeness
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_relation_object_has_all_properties()
    {
        $field = Select::make('user')
            ->relation('users', 'name', 'belongsTo', 'user_id');

        $relation = $this->getPropertyValue($field, 'relation');

        $this->assertObjectHasProperty('name', $relation);
        $this->assertObjectHasProperty('field', $relation);
        $this->assertObjectHasProperty('key', $relation);
        $this->assertObjectHasProperty('type', $relation);
    }
}
