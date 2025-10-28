<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;

class FieldValidationTest extends BaseFieldTestCase
{
    /**
     * Test field has empty rules by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_has_empty_rules_by_default()
    {
        $field = Text::make('email');

        $this->assertEmpty($this->getPropertyValue($field, 'rules'));
        $this->assertEmpty($this->getPropertyValue($field, 'creationRules'));
        $this->assertEmpty($this->getPropertyValue($field, 'updateRules'));
    }

    /**
     * Test rules() method sets common rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_rules_method_sets_rules()
    {
        $field = Text::make('email')
            ->rules('required', 'email');

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertCount(2, $rules);
        $this->assertContains('required', $rules);
        $this->assertContains('email', $rules);
    }

    /**
     * Test rules() also sets creationRules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_rules_also_sets_creation_rules()
    {
        $field = Text::make('email')
            ->rules('required', 'email');

        $creationRules = $this->getPropertyValue($field, 'creationRules');
        $this->assertCount(2, $creationRules);
        $this->assertContains('required', $creationRules);
        $this->assertContains('email', $creationRules);
    }

    /**
     * Test rules() also sets updateRules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_rules_also_sets_update_rules()
    {
        $field = Text::make('email')
            ->rules('required', 'email');

        $updateRules = $this->getPropertyValue($field, 'updateRules');
        $this->assertCount(2, $updateRules);
        $this->assertContains('required', $updateRules);
        $this->assertContains('email', $updateRules);
    }

    /**
     * Test rules() with array parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_rules_with_array()
    {
        $field = Text::make('email')
            ->rules(['required', 'email', 'max:255']);

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertCount(3, $rules);
        $this->assertContains('required', $rules);
        $this->assertContains('email', $rules);
        $this->assertContains('max:255', $rules);
    }

    /**
     * Test creationRules() sets rules only for creation
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_creation_rules_only()
    {
        $field = Text::make('email')
            ->creationRules('required', 'unique:users,email');

        $creationRules = $this->getPropertyValue($field, 'creationRules');
        $this->assertContains('required', $creationRules);
        $this->assertContains('unique:users,email', $creationRules);
    }

    /**
     * Test updateRules() sets rules only for update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_rules_only()
    {
        $field = Text::make('email')
            ->updateRules('required', 'unique:users,email,{id}');

        $updateRules = $this->getPropertyValue($field, 'updateRules');
        $this->assertContains('required', $updateRules);
        $this->assertContains('unique:users,email,{id}', $updateRules);
    }

    /**
     * Test combining rules() with creationRules()
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_combining_rules_and_creation_rules()
    {
        $field = Text::make('email')
            ->rules('required', 'email')
            ->creationRules('unique:users,email');

        $creationRules = $this->getPropertyValue($field, 'creationRules');
        // Should have rules from rules() + unique from creationRules()
        $this->assertContains('required', $creationRules);
        $this->assertContains('email', $creationRules);
        $this->assertContains('unique:users,email', $creationRules);
    }

    /**
     * Test combining rules() with updateRules()
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_combining_rules_and_update_rules()
    {
        $field = Text::make('email')
            ->rules('required', 'email')
            ->updateRules('unique:users,email,{id}');

        $updateRules = $this->getPropertyValue($field, 'updateRules');
        // Should have rules from rules() + unique from updateRules()
        $this->assertContains('required', $updateRules);
        $this->assertContains('email', $updateRules);
        $this->assertContains('unique:users,email,{id}', $updateRules);
    }

    /**
     * Test multiple rules() calls merge rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_multiple_rules_calls_merge()
    {
        $field = Text::make('email')
            ->rules('required')
            ->rules('email');

        $rules = $this->getPropertyValue($field, 'rules');
        // Last call overwrites (not merges for rules property itself)
        // but creationRules should have both
        $this->assertContains('email', $rules);
    }

    /**
     * Test rules with complex validation strings
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_rules_with_complex_strings()
    {
        $field = Text::make('password')
            ->rules('required', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*[0-9])/', 'confirmed');

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertCount(4, $rules);
        $this->assertContains('required', $rules);
        $this->assertContains('min:8', $rules);
        $this->assertContains('confirmed', $rules);
    }

    /**
     * Test creation-only field (immutable after creation)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_immutable_field_pattern()
    {
        $field = Text::make('id')
            ->rules('required', 'unique:users,id')
            ->updateRules(''); // Empty on update

        $creationRules = $this->getPropertyValue($field, 'creationRules');
        $this->assertCount(2, $creationRules);
        $this->assertContains('required', $creationRules);
        $this->assertContains('unique:users,id', $creationRules);
    }

    /**
     * Test nullable field pattern
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_nullable_field_pattern()
    {
        $field = Text::make('middle_name')
            ->rules('nullable', 'string', 'max:100');

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertContains('nullable', $rules);
        $this->assertContains('string', $rules);
        $this->assertContains('max:100', $rules);
    }

    /**
     * Test custom rule (closure or class)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_custom_rule_string()
    {
        $field = Text::make('slug')
            ->rules('required', 'unique:posts,slug', 'regex:/^[a-z0-9-]+$/');

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertContains('regex:/^[a-z0-9-]+$/', $rules);
    }

    /**
     * Test rules can be retrieved via get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_rules_available_in_get_method()
    {
        $field = Text::make('email')
            ->rules('required', 'email');

        $fieldData = $field->get(null);

        $this->assertNotEmpty($fieldData->rules);
        $this->assertContains('required', $fieldData->rules);
        $this->assertContains('email', $fieldData->rules);
    }

    /**
     * Test creationRules available in get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_creation_rules_available_in_get_method()
    {
        $field = Text::make('email')
            ->rules('required', 'email')
            ->creationRules('unique:users,email');

        $fieldData = $field->get(null);

        $this->assertNotEmpty($fieldData->creationRules);
        $this->assertContains('required', $fieldData->creationRules);
        $this->assertContains('unique:users,email', $fieldData->creationRules);
    }

    /**
     * Test updateRules available in get() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_rules_available_in_get_method()
    {
        $field = Text::make('email')
            ->rules('required', 'email')
            ->updateRules('unique:users,email,{id}');

        $fieldData = $field->get(null);

        $this->assertNotEmpty($fieldData->updateRules);
        $this->assertContains('required', $fieldData->updateRules);
        $this->assertContains('unique:users,email,{id}', $fieldData->updateRules);
    }

    /**
     * Test method chaining with rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_validation_method_chaining()
    {
        $field = Text::make('email')
            ->rules('required', 'email')
            ->creationRules('unique:users,email')
            ->updateRules('unique:users,email,{id}')
            ->display('Email Address');

        $this->assertEquals('Email Address', $this->getPropertyValue($field, 'display'));
        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertContains('required', $rules);
    }

    /**
     * Test rules for common field types
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_typical_email_field_validation()
    {
        $field = Text::make('email')
            ->rules('required', 'email', 'unique:users,email')
            ->display('Email Address')
            ->placeholder('user@example.com');

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertCount(3, $rules);
    }

    /**
     * Test rules for password field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_typical_password_field_validation()
    {
        $field = Text::make('password')
            ->rules('required', 'min:8', 'confirmed')
            ->creationRules('required', 'min:8', 'confirmed')
            ->updateRules('nullable', 'min:8', 'confirmed');

        $creationRules = $this->getPropertyValue($field, 'creationRules');
        $updateRules = $this->getPropertyValue($field, 'updateRules');

        $this->assertContains('required', $creationRules);
        $this->assertContains('nullable', $updateRules);
    }

    /**
     * Test rules for name field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_typical_name_field_validation()
    {
        $field = Text::make('first_name')
            ->rules('required', 'string', 'max:50');

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertCount(3, $rules);
        $this->assertContains('required', $rules);
        $this->assertContains('string', $rules);
        $this->assertContains('max:50', $rules);
    }

    /**
     * Test clearing rules pattern
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_no_rules_scenario()
    {
        $field = Text::make('internal_id')
            ->readonly(); // No validation needed

        $rules = $this->getPropertyValue($field, 'rules');
        $this->assertEmpty($rules);
    }
}
