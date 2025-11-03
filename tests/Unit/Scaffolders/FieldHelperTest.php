<?php

namespace Tir\Crud\Tests\Unit\Scaffolders;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\TextArea;
use Tir\Crud\Support\Scaffold\Fields\Password;
use Tir\Crud\Support\Scaffold\Fields\Number;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Fields\CheckBox;
use Tir\Crud\Support\Scaffold\Fields\SwitchBox;
use Tir\Crud\Support\Scaffold\Fields\DatePicker;
use Tir\Crud\Support\Scaffold\Fields\FileUploader;
use Tir\Crud\Support\Scaffold\Fields\Additional;
use Tir\Crud\Support\Scaffold\Fields\ColorPicker;
use Tir\Crud\Support\Scaffold\Fields\Editor;
use Tir\Crud\Support\Scaffold\Fields\Radio;
use Tir\Crud\Support\Scaffold\Fields\Slug;
use Tir\Crud\Support\Scaffold\Fields\Button;
use Tir\Crud\Support\Scaffold\Fields\Link;
use Tir\Crud\Support\Scaffold\Fields\Custom;
use Tir\Crud\Support\Scaffold\Traits\FieldHelper;
use Tir\Crud\Tests\TestCase;

/**
 * Test FieldHelper trait functionality
 *
 * FieldHelper provides convenient factory methods for creating field instances
 * without having to use the full class names.
 */
class FieldHelperTest extends TestCase
{
    /**
     * Test class that uses the FieldHelper trait for testing
     */
    private $fieldHelperInstance;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test class that uses the FieldHelper trait
        $this->fieldHelperInstance = new class {
            use FieldHelper;

            // Make protected methods public for testing
            public function publicText(string $name) { return $this->text($name); }
            public function publicTextArea(string $name) { return $this->textArea($name); }
            public function publicPassword(string $name) { return $this->password($name); }
            public function publicNumber(string $name) { return $this->number($name); }
            public function publicSelect(string $name) { return $this->select($name); }
            public function publicCheckBox(string $name) { return $this->checkBox($name); }
            public function publicSwitchBox(string $name) { return $this->switchBox($name); }
            public function publicDatePicker(string $name) { return $this->datePicker($name); }
            public function publicFileUploader(string $name) { return $this->fileUploader($name); }
            public function publicAdditional(string $name) { return $this->additional($name); }
            public function publicColorPicker(string $name) { return $this->colorPicker($name); }
            public function publicEditor(string $name) { return $this->editor($name); }
            public function publicRadio(string $name) { return $this->radio($name); }
            public function publicSlug(string $name) { return $this->slug($name); }
            public function publicButton(string $name) { return $this->button($name); }
            public function publicLink(string $name) { return $this->link($name); }
            public function publicCustom(string $name) { return $this->custom($name); }
        };
    }

    /**
     * Test text() method creates Text field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_text_method_creates_text_field()
    {
        $field = $this->fieldHelperInstance->publicText('test_field');

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals('test_field', $this->getFieldName($field));
    }

    /**
     * Test textArea() method creates TextArea field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_textArea_method_creates_textArea_field()
    {
        $field = $this->fieldHelperInstance->publicTextArea('description');

        $this->assertInstanceOf(TextArea::class, $field);
        $this->assertEquals('description', $this->getFieldName($field));
    }

    /**
     * Test password() method creates Password field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_password_method_creates_password_field()
    {
        $field = $this->fieldHelperInstance->publicPassword('password');

        $this->assertInstanceOf(Password::class, $field);
        $this->assertEquals('password', $this->getFieldName($field));
    }

    /**
     * Test number() method creates Number field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_number_method_creates_number_field()
    {
        $field = $this->fieldHelperInstance->publicNumber('age');

        $this->assertInstanceOf(Number::class, $field);
        $this->assertEquals('age', $this->getFieldName($field));
    }

    /**
     * Test select() method creates Select field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_select_method_creates_select_field()
    {
        $field = $this->fieldHelperInstance->publicSelect('status');

        $this->assertInstanceOf(Select::class, $field);
        $this->assertEquals('status', $this->getFieldName($field));
    }

    /**
     * Test checkBox() method creates CheckBox field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_checkBox_method_creates_checkBox_field()
    {
        $field = $this->fieldHelperInstance->publicCheckBox('is_active');

        $this->assertInstanceOf(CheckBox::class, $field);
        $this->assertEquals('is_active', $this->getFieldName($field));
    }

    /**
     * Test switchBox() method creates SwitchBox field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_switchBox_method_creates_switchBox_field()
    {
        $field = $this->fieldHelperInstance->publicSwitchBox('enabled');

        $this->assertInstanceOf(SwitchBox::class, $field);
        $this->assertEquals('enabled', $this->getFieldName($field));
    }

    /**
     * Test datePicker() method creates DatePicker field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_datePicker_method_creates_datePicker_field()
    {
        $field = $this->fieldHelperInstance->publicDatePicker('birth_date');

        $this->assertInstanceOf(DatePicker::class, $field);
        $this->assertEquals('birth_date', $this->getFieldName($field));
    }

    /**
     * Test fileUploader() method creates FileUploader field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fileUploader_method_creates_fileUploader_field()
    {
        $field = $this->fieldHelperInstance->publicFileUploader('avatar');

        $this->assertInstanceOf(FileUploader::class, $field);
        $this->assertEquals('avatar', $this->getFieldName($field));
    }

    /**
     * Test additional() method creates Additional field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_additional_method_creates_additional_field()
    {
        $field = $this->fieldHelperInstance->publicAdditional('metadata');

        $this->assertInstanceOf(Additional::class, $field);
        $this->assertEquals('metadata', $this->getFieldName($field));
    }

    /**
     * Test colorPicker() method creates ColorPicker field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_colorPicker_method_creates_colorPicker_field()
    {
        $field = $this->fieldHelperInstance->publicColorPicker('theme_color');

        $this->assertInstanceOf(ColorPicker::class, $field);
        $this->assertEquals('theme_color', $this->getFieldName($field));
    }

    /**
     * Test editor() method creates Editor field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_editor_method_creates_editor_field()
    {
        $field = $this->fieldHelperInstance->publicEditor('content');

        $this->assertInstanceOf(Editor::class, $field);
        $this->assertEquals('content', $this->getFieldName($field));
    }

    /**
     * Test radio() method creates Radio field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_radio_method_creates_radio_field()
    {
        $field = $this->fieldHelperInstance->publicRadio('gender');

        $this->assertInstanceOf(Radio::class, $field);
        $this->assertEquals('gender', $this->getFieldName($field));
    }

    /**
     * Test slug() method creates Slug field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_slug_method_creates_slug_field()
    {
        $field = $this->fieldHelperInstance->publicSlug('url_slug');

        $this->assertInstanceOf(Slug::class, $field);
        $this->assertEquals('url_slug', $this->getFieldName($field));
    }

    /**
     * Test button() method creates Button field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_button_method_creates_button_field()
    {
        $field = $this->fieldHelperInstance->publicButton('submit_btn');

        $this->assertInstanceOf(Button::class, $field);
        $this->assertEquals('submit_btn', $this->getFieldName($field));
    }

    /**
     * Test link() method creates Link field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_link_method_creates_link_field()
    {
        $field = $this->fieldHelperInstance->publicLink('website');

        $this->assertInstanceOf(Link::class, $field);
        $this->assertEquals('website', $this->getFieldName($field));
    }

    /**
     * Test custom() method creates Custom field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_custom_method_creates_custom_field()
    {
        $field = $this->fieldHelperInstance->publicCustom('special_field');

        $this->assertInstanceOf(Custom::class, $field);
        $this->assertEquals('special_field', $this->getFieldName($field));
    }

    /**
     * Helper method to get field name using reflection
     */
    private function getFieldName($field): string
    {
        $reflection = new \ReflectionClass($field);
        $property = $reflection->getProperty('name');
        $property->setAccessible(true);
        return $property->getValue($field);
    }
}
