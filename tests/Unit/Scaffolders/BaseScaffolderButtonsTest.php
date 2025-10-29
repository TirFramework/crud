<?php

namespace Tir\Crud\Tests\Unit\Scaffolders;

use ReflectionProperty;
use Tir\Crud\Tests\TestCase;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Button;

/**
 * Test scaffolder implementation with custom buttons for testing
 */
class TestScaffolderWithButtons extends BaseScaffolder
{
    private ?array $customButtons = null;

    public function __construct(?array $buttons = null)
    {
        $this->customButtons = $buttons;
        parent::__construct();
    }

    protected function setModuleName(): string
    {
        return 'test-module';
    }

    protected function setFields(): array
    {
        return [];
    }

    protected function setModel(): string
    {
        return TestModelButtons::class;
    }

    protected function setButtons(): array
    {
        return $this->customButtons ?? parent::setButtons();
    }
}

/**
 * Simple test model for testing buttons
 */
class TestModelButtons extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['name'];
}

class BaseScaffolderButtonsTest extends TestCase
{
    /**
     * Helper method to get protected property value using Reflection
     */
    protected function getPropertyValue(object $object, string $property): mixed
    {
        $reflection = new ReflectionProperty($object::class, $property);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }

    /**
     * Test that getIndexButtons returns only buttons shown on index
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_index_buttons_returns_only_buttons_shown_on_index()
    {
        $buttons = [
            Button::make('show')->display('Show')->showOnIndex(true),
            Button::make('hide')->display('Hide')->showOnIndex(false),
            Button::make('create')->display('Create')->showOnIndex(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $scaffolder->scaffold('index');

        $indexButtons = $scaffolder->getIndexButtons();

        $this->assertCount(2, $indexButtons);
        $this->assertEquals('show', $indexButtons[0]->name);
        $this->assertEquals('create', $indexButtons[1]->name);
    }

    /**
     * Test that getCreateButtons returns only buttons shown on creating
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_create_buttons_returns_only_buttons_shown_on_creating()
    {
        $buttons = [
            Button::make('save')->display('Save')->showOnCreating(true),
            Button::make('cancel')->display('Cancel')->showOnCreating(false),
            Button::make('back')->display('Back')->showOnCreating(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $scaffolder->scaffold('create');

        $createButtons = $scaffolder->getCreateButtons();

        $this->assertCount(2, $createButtons);
        $this->assertEquals('save', $createButtons[0]->name);
        $this->assertEquals('back', $createButtons[1]->name);
    }

    /**
     * Test that getEditButtons returns only buttons shown on editing
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_edit_buttons_returns_only_buttons_shown_on_editing()
    {
        $buttons = [
            Button::make('update')->display('Update')->showOnEditing(true),
            Button::make('delete')->display('Delete')->showOnEditing(false),
            Button::make('cancel')->display('Cancel')->showOnEditing(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $scaffolder->scaffold('edit');

        $editButtons = $scaffolder->getEditButtons();

        $this->assertCount(2, $editButtons);
        $this->assertEquals('update', $editButtons[0]->name);
        $this->assertEquals('cancel', $editButtons[1]->name);
    }

    /**
     * Test that getDetailButtons returns only buttons shown on detail
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_detail_buttons_returns_only_buttons_shown_on_detail()
    {
        $buttons = [
            Button::make('edit')->display('Edit')->showOnDetail(true),
            Button::make('delete')->display('Delete')->showOnDetail(false),
            Button::make('back')->display('Back')->showOnDetail(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $scaffolder->scaffold('detail');

        $detailButtons = $scaffolder->getDetailButtons();

        $this->assertCount(2, $detailButtons);
        $this->assertEquals('edit', $detailButtons[0]->name);
        $this->assertEquals('back', $detailButtons[1]->name);
    }

    /**
     * Test that addButtonsToScaffold processes buttons correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_add_buttons_to_scaffold_processes_buttons_correctly()
    {
        $buttons = [
            Button::make('test')->display('Test Button'),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $scaffolder->scaffold('create');

        $processedButtons = $scaffolder->getButtons();

        $this->assertIsArray($processedButtons);
        $this->assertCount(1, $processedButtons);
        $this->assertEquals('test', $processedButtons[0]->name);
    }

    /**
     * Test that buttons are included in create scaffold
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_buttons_are_included_in_create_scaffold()
    {
        $buttons = [
            Button::make('save')->display('Save')->showOnCreating(true),
            Button::make('cancel')->display('Cancel')->showOnCreating(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $model = new TestModelButtons();
        $scaffolder->scaffold('create', $model);

        $scaffold = $scaffolder->getCreateScaffold();

        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertIsArray($scaffold['buttons']);
        $this->assertCount(2, $scaffold['buttons']);
    }

    /**
     * Test that buttons are included in edit scaffold
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_buttons_are_included_in_edit_scaffold()
    {
        $buttons = [
            Button::make('update')->display('Update')->showOnEditing(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $model = new TestModelButtons();
        $scaffolder->scaffold('edit', $model);

        $scaffold = $scaffolder->getEditScaffold();

        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertIsArray($scaffold['buttons']);
        $this->assertCount(1, $scaffold['buttons']);
    }

    /**
     * Test that buttons are included in detail scaffold
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_buttons_are_included_in_detail_scaffold()
    {
        $buttons = [
            Button::make('back')->display('Back')->showOnDetail(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $model = new TestModelButtons();
        $scaffolder->scaffold('detail', $model);

        $scaffold = $scaffolder->getDetailScaffold();

        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertIsArray($scaffold['buttons']);
        $this->assertCount(1, $scaffold['buttons']);
    }

    /**
     * Test that buttons are included in index scaffold
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_buttons_are_included_in_index_scaffold()
    {
        $buttons = [
            Button::make('create')->display('Create New')->showOnIndex(true),
        ];

        $scaffolder = new TestScaffolderWithButtons($buttons);
        $model = new TestModelButtons();
        $scaffolder->scaffold('index', $model);

        $scaffold = $scaffolder->getIndexScaffold();

        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertIsArray($scaffold['buttons']);
        $this->assertCount(1, $scaffold['buttons']);
    }

    /**
     * Test that hideFromDetail method works correctly on buttons
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_from_detail_method_works_correctly_on_buttons()
    {
        $button = Button::make('submit')->display('Submit')->hideFromDetail();

        $this->assertTrue($this->getPropertyValue($button, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($button, 'showOnCreating'));
        $this->assertTrue($this->getPropertyValue($button, 'showOnEditing'));
        $this->assertFalse($this->getPropertyValue($button, 'showOnDetail'));
    }

    /**
     * Test that hideFromIndex method works correctly on buttons
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_from_index_method_works_correctly_on_buttons()
    {
        $button = Button::make('delete')->display('Delete')->hideFromIndex();

        $this->assertFalse($this->getPropertyValue($button, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($button, 'showOnCreating'));
        $this->assertTrue($this->getPropertyValue($button, 'showOnEditing'));
        $this->assertTrue($this->getPropertyValue($button, 'showOnDetail'));
    }

    /**
     * Test that onlyOnIndex method works correctly on buttons
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_on_index_method_works_correctly_on_buttons()
    {
        $button = Button::make('export')->display('Export')->onlyOnIndex();

        $this->assertTrue($this->getPropertyValue($button, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($button, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($button, 'showOnEditing'));
        $this->assertFalse($this->getPropertyValue($button, 'showOnDetail'));
    }

    /**
     * Test that onlyOnCreating method works correctly on buttons
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_on_creating_method_works_correctly_on_buttons()
    {
        $button = Button::make('draft')->display('Save Draft')->onlyOnCreating();

        $this->assertFalse($this->getPropertyValue($button, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($button, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($button, 'showOnEditing'));
        $this->assertFalse($this->getPropertyValue($button, 'showOnDetail'));
    }

    /**
     * Test default buttons configuration
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_default_buttons_configuration()
    {
        $scaffolder = new TestScaffolderWithButtons(); // Uses default setButtons()
        $scaffolder->scaffold('create');

        $buttons = $scaffolder->getButtons();

        $this->assertCount(2, $buttons);
        $this->assertEquals('back', $buttons[0]->name);
        $this->assertEquals('submit', $buttons[1]->name);

        // Check that submit button is hidden from detail
        $this->assertTrue($buttons[0]->showOnDetail); // back button shown on detail
        $this->assertFalse($buttons[1]->showOnDetail); // submit button hidden from detail
    }

    /**
     * Test that getButtons returns empty array before scaffold
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_buttons_returns_empty_array_before_scaffold()
    {
        $scaffolder = new TestScaffolderWithButtons([]);

        $buttons = $scaffolder->getButtons();

        $this->assertIsArray($buttons);
        $this->assertEmpty($buttons);
    }
}
