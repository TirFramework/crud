<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\TextArea;
use Tir\Crud\Support\Scaffold\Fields\Select;

class BaseFieldTest extends BaseFieldTestCase
{
    /**
     * Test that a field can be created using make() method
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_instantiated_with_make()
    {
        $field = Text::make('email');

        $this->assertNotNull($field);
        $this->assertInstanceOf(Text::class, $field);
    }

    /**
     * Test that field name is set correctly from make()
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_name_is_set_correctly()
    {
        $field = Text::make('email');

        $this->assertEquals('email', $this->getPropertyValue($field, 'name'));
        $this->assertEquals('email', $this->getPropertyValue($field, 'originalName'));
        $this->assertEquals('email', $this->getPropertyValue($field, 'request'));
    }

    /**
     * Test that display label is auto-generated from name
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_display_label_is_auto_generated()
    {
        // Test simple name
        $field = Text::make('email');
        $this->assertEquals('Email', $this->getPropertyValue($field, 'display'));

        // Test underscore conversion
        $field = Text::make('first_name');
        $this->assertEquals('First Name', $this->getPropertyValue($field, 'display'));

        // Test multiple underscores
        $field = Text::make('user_email_address');
        $this->assertEquals('User Email Address', $this->getPropertyValue($field, 'display'));
    }

    /**
     * Test that className is formatted correctly (dots to dashes)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_class_name_is_formatted_correctly()
    {
        // Test simple name
        $field = Text::make('email');
        $this->assertEquals('email', $this->getPropertyValue($field, 'className'));

        // Test with dots (converted to dashes)
        $field = Text::make('user.email.address');
        $this->assertEquals('user-email-address', $this->getPropertyValue($field, 'className'));
    }

    /**
     * Test that display() method can override auto-generated display
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_display_method_overrides_default()
    {
        $field = Text::make('email')
            ->display('Email Address');

        $this->assertEquals('Email Address', $this->getPropertyValue($field, 'display'));
    }

    /**
     * Test that page() method sets the page property correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_page_method_sets_page_property()
    {
        $field = Text::make('email')
            ->page('create');

        $this->assertEquals('create', $this->getPropertyValue($field, 'page'));
    }

    /**
     * Test that page() method supports method chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_page_method_supports_chaining()
    {
        $field = Text::make('email')
            ->page('edit')
            ->display('Email Field');

        $this->assertEquals('edit', $this->getPropertyValue($field, 'page'));
        $this->assertEquals('Email Field', $this->getPropertyValue($field, 'display'));
    }

    /**
     * Test that page() method can be called multiple times (last call wins)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_page_method_overwrites_previous_value()
    {
        $field = Text::make('email')
            ->page('create')
            ->page('edit')
            ->page('show');

        $this->assertEquals('show', $this->getPropertyValue($field, 'page'));
    }

    /**
     * Test fluent interface - methods return $this for chaining
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_supports_method_chaining()
    {
        $field = Text::make('email')
            ->display('Email Address')
            ->placeholder('Enter your email')
            ->rules('required', 'email');

        $this->assertEquals('Email Address', $this->getPropertyValue($field, 'display'));
        $this->assertEquals('Enter your email', $this->getPropertyValue($field, 'placeholder'));
        $this->assertNotEmpty($this->getPropertyValue($field, 'rules'));
    }

    /**
     * Test field with default value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_have_default_value()
    {
        $field = Text::make('status')
            ->default('active');

        $this->assertEquals('active', $this->getPropertyValue($field, 'defaultValue'));
    }

    /**
     * Test field can be disabled
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_disabled()
    {
        $field = Text::make('email')
            ->disable();

        $this->assertTrue($this->getPropertyValue($field, 'disable'));
    }

    /**
     * Test field can be set to readonly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_readonly()
    {
        $field = Text::make('id')
            ->readonly();

        $this->assertTrue($this->getPropertyValue($field, 'readonly'));
    }

    /**
     * Test field can have placeholder text
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_have_placeholder()
    {
        $field = Text::make('email')
            ->placeholder('user@example.com');

        $this->assertEquals('user@example.com', $this->getPropertyValue($field, 'placeholder'));
    }

    /**
     * Test field can have CSS classes
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_have_css_classes()
    {
        $field = Text::make('email')
            ->class('form-control')
            ->class('is-required');

        $this->assertStringContainsString('form-control', $this->getPropertyValue($field, 'class'));
        $this->assertStringContainsString('is-required', $this->getPropertyValue($field, 'class'));
    }

    /**
     * Test field can have column width setting
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_have_column_width()
    {
        $field = Text::make('email')
            ->col(12);

        $this->assertEquals(12, $this->getPropertyValue($field, 'col'));
    }

    /**
     * Test field can have comment/help text
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_have_comment()
    {
        $field = Text::make('email')
            ->comment('Enter a valid email address', 'Email Help');

        $comment = $this->getPropertyValue($field, 'comment');
        $this->assertEquals('Email Help', $comment['title']);
        $this->assertEquals('Enter a valid email address', $comment['content']);
    }

    /**
     * Test field can be marked as sortable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_sortable()
    {
        $field = Text::make('name')
            ->sortable();

        $this->assertTrue($this->getPropertyValue($field, 'sortable'));
    }

    /**
     * Test field can be marked as searchable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_searchable()
    {
        $field = Text::make('name')
            ->searchable();

        $this->assertTrue($this->getPropertyValue($field, 'searchable'));
    }

    /**
     * Test field can be marked as virtual
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_virtual()
    {
        $field = Text::make('full_name')
            ->virtual();

        $this->assertTrue($this->getPropertyValue($field, 'virtual'));
        $this->assertTrue($this->getPropertyValue($field, 'readonly'));
        $this->assertFalse($this->getPropertyValue($field, 'fillable'));
    }

    /**
     * Test field fillable property
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_fillable_property()
    {
        $fillableField = Text::make('email')
            ->fillable(true);

        $this->assertTrue($this->getPropertyValue($fillableField, 'fillable'));

        $nonFillableField = Text::make('id')
            ->fillable(false);

        $this->assertFalse($this->getPropertyValue($nonFillableField, 'fillable'));
    }

    /**
     * Test field can be marked as multiple
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_can_be_multiple()
    {
        $field = Text::make('roles')
            ->multiple();

        $this->assertTrue($this->getPropertyValue($field, 'multiple'));
        $this->assertEquals('array', $this->getPropertyValue($field, 'valueType'));
    }

    /**
     * Test field get() method returns object with field data
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_get_method_returns_object()
    {
        $field = Text::make('email');
        $result = $field->get(null);

        $this->assertIsObject($result);
        $this->assertEquals('email', $result->name);
        $this->assertEquals('Email', $result->display);
    }
}
