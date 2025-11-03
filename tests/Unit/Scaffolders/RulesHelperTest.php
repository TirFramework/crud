<?php

namespace Tir\Crud\Tests\Unit\Scaffolders;

use Tir\Crud\Tests\TestCase;
use Tir\Crud\Support\Scaffold\Traits\RulesHelper;

/**
 * Test class for RulesHelper trait
 */
class TestRulesHelper
{
    use RulesHelper;

    // Mock fields handler for testing
    private $mockFieldsHandler;

    public function setFieldsHandler($handler)
    {
        $this->mockFieldsHandler = $handler;
    }

    protected function fieldsHandler()
    {
        return $this->mockFieldsHandler;
    }
}

/**
 * Mock field class for testing
 */
class MockField
{
    public $name;
    public $creationRules;
    public $updateRules;
    public $showOnIndex;

    public function __construct($name, $creationRules = null, $updateRules = null, $showOnIndex = false)
    {
        $this->name = $name;
        $this->creationRules = $creationRules;
        $this->updateRules = $updateRules;
        $this->showOnIndex = $showOnIndex;
    }
}

/**
 * Mock fields handler for testing
 */
class MockFieldsHandler
{
    private $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function getAllDataFields()
    {
        return $this->fields;
    }
}

class RulesHelperTest extends \Tir\Crud\Tests\TestCase
{
    private $testHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testHelper = new TestRulesHelper();
    }

    /**
     * Test getCreationRules returns rules for fields with creationRules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_creation_rules_returns_rules_for_fields_with_creation_rules()
    {
        $fields = [
            new MockField('name', 'required|string|max:255'),
            new MockField('email', 'required|email'),
            new MockField('age'), // No creation rules
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getCreationRules();

        $expected = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ];

        $this->assertEquals($expected, $rules);
    }

    /**
     * Test getCreationRules returns empty array when no fields have creation rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_creation_rules_returns_empty_array_when_no_fields_have_creation_rules()
    {
        $fields = [
            new MockField('name'),
            new MockField('email'),
            new MockField('age'),
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getCreationRules();

        $this->assertEquals([], $rules);
    }

    /**
     * Test getCreationRules with empty fields array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_creation_rules_with_empty_fields_array()
    {
        $fields = [];
        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getCreationRules();

        $this->assertEquals([], $rules);
    }

    /**
     * Test getUpdateRules returns rules for fields with updateRules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_update_rules_returns_rules_for_fields_with_update_rules()
    {
        $fields = [
            new MockField('name', null, 'required|string|max:255'),
            new MockField('email', null, 'required|email'),
            new MockField('age'), // No update rules
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getUpdateRules();

        $expected = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ];

        $this->assertEquals($expected, $rules);
    }

    /**
     * Test getUpdateRules returns empty array when no fields have update rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_update_rules_returns_empty_array_when_no_fields_have_update_rules()
    {
        $fields = [
            new MockField('name'),
            new MockField('email'),
            new MockField('age'),
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getUpdateRules();

        $this->assertEquals([], $rules);
    }

    /**
     * Test getInlineUpdateRules returns rules only for fields shown on index with update rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_inline_update_rules_returns_rules_only_for_index_fields_with_update_rules()
    {
        $fields = [
            new MockField('name', null, 'required|string|max:255', true), // showOnIndex = true
            new MockField('email', null, 'required|email', true), // showOnIndex = true
            new MockField('age', null, 'nullable|integer|min:0', false), // showOnIndex = false
            new MockField('status', null, null, true), // showOnIndex = true but no update rules
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getInlineUpdateRules();

        $expected = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ];

        $this->assertEquals($expected, $rules);
    }

    /**
     * Test getInlineUpdateRules excludes fields not shown on index
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_inline_update_rules_excludes_fields_not_shown_on_index()
    {
        $fields = [
            new MockField('name', null, 'required|string|max:255', false), // showOnIndex = false
            new MockField('email', null, 'required|email', true), // showOnIndex = true
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getInlineUpdateRules();

        $expected = [
            'email' => 'required|email',
        ];

        $this->assertEquals($expected, $rules);
    }

    /**
     * Test getInlineUpdateRules returns empty array when no eligible fields
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_inline_update_rules_returns_empty_array_when_no_eligible_fields()
    {
        $fields = [
            new MockField('name', null, 'required|string|max:255', false), // showOnIndex = false
            new MockField('email', null, null, true), // showOnIndex = true but no update rules
            new MockField('age', null, null, false), // neither
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        $rules = $this->testHelper->getInlineUpdateRules();

        $this->assertEquals([], $rules);
    }

    /**
     * Test mixed scenario with all rule types
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mixed_scenario_with_all_rule_types()
    {
        $fields = [
            new MockField('name', 'required|string|max:100', 'required|string|max:100', true),
            new MockField('email', 'required|email|unique:users', 'required|email|unique:users,email', true),
            new MockField('age', 'nullable|integer|min:0', 'nullable|integer|min:0', false),
            new MockField('status', null, 'required|in:active,inactive', true),
        ];

        $fieldsHandler = new MockFieldsHandler($fields);
        $this->testHelper->setFieldsHandler($fieldsHandler);

        // Test creation rules
        $creationRules = $this->testHelper->getCreationRules();
        $expectedCreation = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'age' => 'nullable|integer|min:0',
        ];
        $this->assertEquals($expectedCreation, $creationRules);

        // Test update rules
        $updateRules = $this->testHelper->getUpdateRules();
        $expectedUpdate = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'age' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];
        $this->assertEquals($expectedUpdate, $updateRules);

        // Test inline update rules (only fields shown on index)
        $inlineRules = $this->testHelper->getInlineUpdateRules();
        $expectedInline = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'status' => 'required|in:active,inactive',
        ];
        $this->assertEquals($expectedInline, $inlineRules);
    }
}
