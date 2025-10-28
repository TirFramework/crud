<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use Tir\Crud\Support\Scaffold\Fields\Text;

class FieldVisibilityTest extends BaseFieldTestCase
{
    /**
     * Test field shows on index by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_shows_on_index_by_default()
    {
        $field = Text::make('email');

        $this->assertTrue($this->getPropertyValue($field, 'showOnIndex'));
    }

    /**
     * Test field shows on detail by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_shows_on_detail_by_default()
    {
        $field = Text::make('email');

        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
    }

    /**
     * Test field shows on creating by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_shows_on_creating_by_default()
    {
        $field = Text::make('email');

        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
    }

    /**
     * Test field shows on editing by default
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_field_shows_on_editing_by_default()
    {
        $field = Text::make('email');

        $this->assertTrue($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test hideFromIndex() hides field from index view
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_from_index()
    {
        $field = Text::make('email')
            ->hideFromIndex();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        // Other views should still be visible
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test hideFromDetail() hides field from detail view
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_from_detail()
    {
        $field = Text::make('email')
            ->hideFromDetail();

        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        // Other views should still be visible
        $this->assertTrue($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test hideWhenCreating() hides field from create form
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_when_creating()
    {
        $field = Text::make('email')
            ->hideWhenCreating();

        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        // Other views should still be visible
        $this->assertTrue($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test hideWhenEditing() hides field from edit form
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_when_editing()
    {
        $field = Text::make('email')
            ->hideWhenEditing();

        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
        // Other views should still be visible
        $this->assertTrue($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
    }

    /**
     * Test hideFromAll() hides field from all views
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hide_from_all()
    {
        $field = Text::make('email')
            ->hideFromAll();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test showOnIndex() explicitly shows field on index
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_on_index()
    {
        $field = Text::make('email')
            ->hideFromAll()
            ->showOnIndex();

        $this->assertTrue($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test showOnDetail() explicitly shows field on detail
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_on_detail()
    {
        $field = Text::make('email')
            ->hideFromAll()
            ->showOnDetail();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test showOnCreating() explicitly shows field on create form
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_on_creating()
    {
        $field = Text::make('email')
            ->hideFromAll()
            ->showOnCreating();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test showOnEditing() explicitly shows field on edit form
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_on_editing()
    {
        $field = Text::make('email')
            ->hideFromAll()
            ->showOnEditing();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test onlyOnIndex() shows field only on index
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_on_index()
    {
        $field = Text::make('email')
            ->onlyOnIndex();

        $this->assertTrue($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test onlyOnCreating() shows field only on create form
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_on_creating()
    {
        $field = Text::make('email')
            ->onlyOnCreating();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test onlyOnEditing() shows field only on edit form
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_on_editing()
    {
        $field = Text::make('email')
            ->onlyOnEditing();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test onlyOnDetail() shows field only on detail view
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_on_detail()
    {
        $field = Text::make('email')
            ->onlyOnDetail();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test method chaining with visibility methods
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_visibility_method_chaining()
    {
        $field = Text::make('email')
            ->hideFromIndex()
            ->hideWhenEditing()
            ->showOnDetail();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }

    /**
     * Test multiple hideFromIndex calls (idempotent)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_multiple_hide_calls_are_idempotent()
    {
        $field = Text::make('email')
            ->hideFromIndex()
            ->hideFromIndex();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
    }

    /**
     * Test multiple onlyOn calls override previous setting
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_multiple_only_on_calls_override()
    {
        $field = Text::make('email')
            ->onlyOnIndex()
            ->onlyOnDetail();

        $this->assertFalse($this->getPropertyValue($field, 'showOnIndex'));
        $this->assertTrue($this->getPropertyValue($field, 'showOnDetail'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnCreating'));
        $this->assertFalse($this->getPropertyValue($field, 'showOnEditing'));
    }
}
