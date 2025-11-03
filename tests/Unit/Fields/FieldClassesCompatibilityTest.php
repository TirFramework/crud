<?php

namespace Tir\Crud\Tests\Unit\Fields;

use Tir\Crud\Support\Scaffold\Fields\BaseField;

/**
 * Test compatibility and basic functionality of all field classes
 *
 * This test ensures all field classes can be instantiated and work correctly
 * with the BaseField interface, catching syntax errors and inheritance issues.
 */
class FieldClassesCompatibilityTest extends \Tir\Crud\Tests\TestCase
{
    /**
     * List of all field classes to test
     */
    private array $fieldClasses = [
        \Tir\Crud\Support\Scaffold\Fields\Additional::class,
        \Tir\Crud\Support\Scaffold\Fields\Blank::class,
        \Tir\Crud\Support\Scaffold\Fields\Button::class,
        \Tir\Crud\Support\Scaffold\Fields\CheckBox::class,
        \Tir\Crud\Support\Scaffold\Fields\ColorPicker::class,
        \Tir\Crud\Support\Scaffold\Fields\Custom::class,
        \Tir\Crud\Support\Scaffold\Fields\DatePicker::class,
        \Tir\Crud\Support\Scaffold\Fields\Editor::class,
        \Tir\Crud\Support\Scaffold\Fields\FileUploader::class,
        \Tir\Crud\Support\Scaffold\Fields\Group::class,
        \Tir\Crud\Support\Scaffold\Fields\Link::class,
        \Tir\Crud\Support\Scaffold\Fields\Number::class,
        \Tir\Crud\Support\Scaffold\Fields\Password::class,
        \Tir\Crud\Support\Scaffold\Fields\Price::class,
        \Tir\Crud\Support\Scaffold\Fields\Radio::class,
        \Tir\Crud\Support\Scaffold\Fields\Select::class,
        \Tir\Crud\Support\Scaffold\Fields\Slug::class,
        \Tir\Crud\Support\Scaffold\Fields\Step::class,
        \Tir\Crud\Support\Scaffold\Fields\SwitchBox::class,
        \Tir\Crud\Support\Scaffold\Fields\Text::class,
        \Tir\Crud\Support\Scaffold\Fields\TextArea::class,
    ];

    /**
     * Test that all field classes can be instantiated
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_all_field_classes_can_be_instantiated()
    {
        foreach ($this->fieldClasses as $fieldClass) {
            $field = $fieldClass::make('test_field');
            $this->assertNotNull($field);
            $this->assertInstanceOf($fieldClass, $field);
            $this->assertInstanceOf(BaseField::class, $field);
        }
    }

    /**
     * Test that all field classes inherit from BaseField
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_all_field_classes_inherit_from_base_field()
    {
        foreach ($this->fieldClasses as $fieldClass) {
            $this->assertTrue(is_subclass_of($fieldClass, BaseField::class),
                "$fieldClass must inherit from BaseField");
        }
    }

    /**
     * Test basic BaseField functionality for all field classes
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_basic_base_field_functionality()
    {
        foreach ($this->fieldClasses as $fieldClass) {
            $field = $fieldClass::make('test_field');

            // Test name property using reflection (protected)
            $nameProperty = new \ReflectionProperty($field, 'name');
            $nameProperty->setAccessible(true);
            $this->assertEquals('test_field', $nameProperty->getValue($field));

            // Test display method
            $result = $field->display('Test Label');
            $this->assertSame($field, $result);

            // Test that display property is set using reflection
            $displayProperty = new \ReflectionProperty($field, 'display');
            $displayProperty->setAccessible(true);
            $this->assertEquals('Test Label', $displayProperty->getValue($field));
        }
    }

    /**
     * Test that field classes don't have syntax errors
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_classes_syntax_validity()
    {
        foreach ($this->fieldClasses as $fieldClass) {
            // If we can instantiate the class, it means there are no syntax errors
            $field = $fieldClass::make('test_field');
            $this->assertNotNull($field);
            $this->assertTrue(class_exists($fieldClass));
        }
    }
}
