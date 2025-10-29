<?php

namespace Tir\Crud\Tests\Unit\Scaffolders;

use Tir\Crud\Tests\TestCase;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;

class ActionsTest extends TestCase
{
    /**
     * Test that all() returns all actions enabled
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_all_returns_all_actions_enabled()
    {
        $actions = Actions::all();

        $this->assertIsArray($actions);
        $this->assertCount(8, $actions); // All 8 ActionType cases

        // Verify all are enabled
        $this->assertTrue($actions[ActionType::INDEX->value]);
        $this->assertTrue($actions[ActionType::CREATE->value]);
        $this->assertTrue($actions[ActionType::SHOW->value]);
        $this->assertTrue($actions[ActionType::EDIT->value]);
        $this->assertTrue($actions[ActionType::INLINE_EDIT->value]);
        $this->assertTrue($actions[ActionType::DESTROY->value]);
        $this->assertTrue($actions[ActionType::FORCE_DELETE->value]);
        $this->assertTrue($actions[ActionType::RESTORE->value]);
    }

    /**
     * Test that none() returns all actions disabled
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_none_returns_all_actions_disabled()
    {
        $actions = Actions::none();

        $this->assertIsArray($actions);
        $this->assertCount(8, $actions);

        // Verify all are disabled
        foreach ($actions as $enabled) {
            $this->assertFalse($enabled);
        }
    }

    /**
     * Test that basic() returns correct basic CRUD actions
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_basic_returns_correct_basic_actions()
    {
        $actions = Actions::basic();

        $this->assertIsArray($actions);

        // Should enable: INDEX, CREATE, SHOW, EDIT
        $this->assertTrue($actions[ActionType::INDEX->value]);
        $this->assertTrue($actions[ActionType::CREATE->value]);
        $this->assertTrue($actions[ActionType::SHOW->value]);
        $this->assertTrue($actions[ActionType::EDIT->value]);

        // Should disable others
        $this->assertFalse($actions[ActionType::INLINE_EDIT->value]);
        $this->assertFalse($actions[ActionType::DESTROY->value]);
        $this->assertFalse($actions[ActionType::FORCE_DELETE->value]);
        $this->assertFalse($actions[ActionType::RESTORE->value]);
    }

    /**
     * Test that readOnly() returns only index and show actions
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_readonly_returns_only_index_and_show()
    {
        $actions = Actions::readOnly();

        $this->assertIsArray($actions);

        // Should enable only INDEX and SHOW
        $this->assertTrue($actions[ActionType::INDEX->value]);
        $this->assertTrue($actions[ActionType::SHOW->value]);

        // Should disable all others
        $this->assertFalse($actions[ActionType::CREATE->value]);
        $this->assertFalse($actions[ActionType::EDIT->value]);
        $this->assertFalse($actions[ActionType::INLINE_EDIT->value]);
        $this->assertFalse($actions[ActionType::DESTROY->value]);
        $this->assertFalse($actions[ActionType::FORCE_DELETE->value]);
        $this->assertFalse($actions[ActionType::RESTORE->value]);
    }

    /**
     * Test that only() with enum actions works correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_with_enum_actions_works_correctly()
    {
        $actions = Actions::only(ActionType::INDEX, ActionType::CREATE);

        $this->assertIsArray($actions);

        // Should enable only specified actions
        $this->assertTrue($actions[ActionType::INDEX->value]);
        $this->assertTrue($actions[ActionType::CREATE->value]);

        // Should disable all others
        $this->assertFalse($actions[ActionType::SHOW->value]);
        $this->assertFalse($actions[ActionType::EDIT->value]);
        $this->assertFalse($actions[ActionType::INLINE_EDIT->value]);
        $this->assertFalse($actions[ActionType::DESTROY->value]);
        $this->assertFalse($actions[ActionType::FORCE_DELETE->value]);
        $this->assertFalse($actions[ActionType::RESTORE->value]);
    }

    /**
     * Test that only() with string actions works correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_with_string_actions_works_correctly()
    {
        $actions = Actions::only('index', 'create');

        $this->assertIsArray($actions);

        // Should enable only specified actions
        $this->assertTrue($actions['index']);
        $this->assertTrue($actions['create']);

        // Should disable all others
        $this->assertFalse($actions['show']);
        $this->assertFalse($actions['edit']);
        $this->assertFalse($actions['inlineEdit']);
        $this->assertFalse($actions['destroy']);
        $this->assertFalse($actions['forceDelete']);
        $this->assertFalse($actions['restore']);
    }

    /**
     * Test that only() with mixed enum and string actions works
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_only_with_mixed_enum_and_string_actions()
    {
        $actions = Actions::only(ActionType::INDEX, 'create', 'custom-action');

        $this->assertIsArray($actions);

        // Should enable specified actions
        $this->assertTrue($actions[ActionType::INDEX->value]);
        $this->assertTrue($actions['create']);
        $this->assertTrue($actions['custom-action']);

        // Should disable standard actions not specified
        $this->assertFalse($actions[ActionType::SHOW->value]);
        $this->assertFalse($actions[ActionType::EDIT->value]);
    }

    /**
     * Test that except() disables specified actions
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_except_disables_specified_actions()
    {
        $actions = Actions::except(ActionType::DESTROY, ActionType::FORCE_DELETE);

        $this->assertIsArray($actions);

        // Should disable specified actions
        $this->assertFalse($actions[ActionType::DESTROY->value]);
        $this->assertFalse($actions[ActionType::FORCE_DELETE->value]);

        // Should enable all others
        $this->assertTrue($actions[ActionType::INDEX->value]);
        $this->assertTrue($actions[ActionType::CREATE->value]);
        $this->assertTrue($actions[ActionType::SHOW->value]);
        $this->assertTrue($actions[ActionType::EDIT->value]);
        $this->assertTrue($actions[ActionType::INLINE_EDIT->value]);
        $this->assertTrue($actions[ActionType::RESTORE->value]);
    }

    /**
     * Test that isEnabled() correctly checks action status
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_is_enabled_correctly_checks_action_status()
    {
        $config = [
            'index' => true,
            'create' => false,
            'edit' => true,
        ];

        $this->assertTrue(Actions::isEnabled($config, ActionType::INDEX));
        $this->assertFalse(Actions::isEnabled($config, ActionType::CREATE));
        $this->assertTrue(Actions::isEnabled($config, 'edit'));
        $this->assertFalse(Actions::isEnabled($config, 'nonexistent'));
    }

    /**
     * Test that getEnabled() returns enabled action names
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_enabled_returns_enabled_action_names()
    {
        $config = [
            'index' => true,
            'create' => false,
            'edit' => true,
            'custom-action' => true,
        ];

        $enabled = Actions::getEnabled($config);

        $this->assertIsArray($enabled);
        $this->assertContains('index', $enabled);
        $this->assertContains('edit', $enabled);
        $this->assertContains('custom-action', $enabled);
        $this->assertNotContains('create', $enabled);
    }

    /**
     * Test that merge() combines configurations correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_merge_combines_configurations_correctly()
    {
        $config1 = ['index' => false, 'create' => true];
        $config2 = ['create' => false, 'edit' => true];

        $merged = Actions::merge($config1, $config2);

        $this->assertIsArray($merged);
        $this->assertFalse($merged['index']); // From config1
        $this->assertFalse($merged['create']); // Overridden by config2
        $this->assertTrue($merged['edit']); // From config2
        $this->assertTrue($merged['show']); // Default true
    }

    /**
     * Test that isValidAction() validates action names correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_is_valid_action_validates_correctly()
    {
        $this->assertTrue(Actions::isValidAction(ActionType::INDEX));
        $this->assertTrue(Actions::isValidAction('index'));
        $this->assertTrue(Actions::isValidAction('create'));
        $this->assertFalse(Actions::isValidAction('invalid-action'));
        $this->assertFalse(Actions::isValidAction(''));
    }

    /**
     * Test that values() returns all action values
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_values_returns_all_action_values()
    {
        $values = Actions::values();

        $this->assertIsArray($values);
        $this->assertCount(8, $values);
        $this->assertContains('index', $values);
        $this->assertContains('create', $values);
        $this->assertContains('show', $values);
        $this->assertContains('edit', $values);
        $this->assertContains('inlineEdit', $values);
        $this->assertContains('destroy', $values);
        $this->assertContains('forceDelete', $values);
        $this->assertContains('restore', $values);
    }

    /**
     * Test that getActionTypes() returns all enum cases
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_action_types_returns_all_enum_cases()
    {
        $types = Actions::getActionTypes();

        $this->assertIsArray($types);
        $this->assertCount(8, $types);

        foreach ($types as $type) {
            $this->assertInstanceOf(ActionType::class, $type);
        }
    }

    /**
     * Test that addCustom() adds custom actions to base config
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_add_custom_adds_custom_actions_to_base_config()
    {
        $baseConfig = Actions::basic();
        $enhanced = Actions::addCustom($baseConfig, 'bulk-edit', 'export');

        $this->assertIsArray($enhanced);
        $this->assertTrue($enhanced['bulk-edit']);
        $this->assertTrue($enhanced['export']);

        // Original actions should still be enabled
        $this->assertTrue($enhanced[ActionType::INDEX->value]);
        $this->assertTrue($enhanced[ActionType::CREATE->value]);
    }

    /**
     * Test that mixed() combines enum and custom actions
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mixed_combines_enum_and_custom_actions()
    {
        $mixed = Actions::mixed([ActionType::INDEX, ActionType::EDIT], ['custom1', 'custom2']);

        $this->assertIsArray($mixed);
        $this->assertTrue($mixed[ActionType::INDEX->value]);
        $this->assertTrue($mixed[ActionType::EDIT->value]);
        $this->assertTrue($mixed['custom1']);
        $this->assertTrue($mixed['custom2']);

        // Other actions should be disabled
        $this->assertFalse($mixed[ActionType::CREATE->value]);
        $this->assertFalse($mixed[ActionType::SHOW->value]);
    }
}
