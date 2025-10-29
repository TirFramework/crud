<?php

namespace Tir\Crud\Tests\Integration\Scaffolders;

use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Fields\Group;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Tests\Integration\Scaffolders\TestModels\TestUser;

/**
 * Integration test for field interactions within a scaffolder
 *
 * This test verifies how multiple fields work together in a complete form configuration,
 * including data flow, validation rules, and field relationships.
 */
class FieldIntegrationTest extends \Tir\Crud\Tests\TestCase
{
    /**
     * Test complete field integration in a scaffolder
     */
    public function test_complete_field_integration_in_scaffolder()
    {
        // Create a test scaffolder with multiple field types
        $scaffolder = new class extends BaseScaffolder {
            protected function setFields(): array
            {
                return [
                    Text::make('first_name')
                        ->display('First Name')
                        ->rules('required', 'min:2')
                        ->searchable()
                        ->filter(),

                    Text::make('last_name')
                        ->display('Last Name')
                        ->rules('required')
                        ->searchable()
                        ->filter(),

                    Text::make('email')
                        ->rules('required', 'email', 'unique:users,email')
                        ->searchable()
                        ->filter()
                        ->sortable(),

                    Select::make('status')
                        ->data(
                            ['value' => 'active', 'label' => 'Active'],
                            ['value' => 'inactive', 'label' => 'Inactive']
                        )
                        ->filter()
                        ->sortable(),

                    Group::make('contact_info')
                        ->display('Contact Information')
                        ->children(
                            Text::make('phone')->rules('nullable', 'regex:/^[0-9+\-\s()]+$/'),
                            Text::make('address')->rules('nullable')
                        )
                        ->col(12)
                ];
            }

            protected function setModel(): string
            {
                return TestUser::class;
            }

            protected function setModuleName(): string
            {
                return 'user';
            }
        };

        // Initialize the scaffolder for create page
        $scaffolder->scaffold('create');

        // Test field configuration
        $fields = $scaffolder->getCreateFields();

        // Verify field count and types
        $this->assertCount(5, $fields);

        // Test first_name field configuration
        $firstNameField = $fields[0];
        $this->assertEquals('first_name', $firstNameField->name);
        $this->assertEquals('First Name', $firstNameField->display);
        $this->assertTrue($firstNameField->searchable);
        $this->assertTrue($firstNameField->filterable);
        $this->assertContains('required', $firstNameField->rules);
        $this->assertContains('min:2', $firstNameField->rules);

        // Test email field configuration
        $emailField = $fields[2];
        $this->assertEquals('email', $emailField->name);
        $this->assertTrue($emailField->searchable);
        $this->assertTrue($emailField->sortable);
        $this->assertContains('required', $emailField->rules);
        $this->assertContains('email', $emailField->rules);

        // Test select field data
        $statusField = $fields[3];
        $this->assertEquals('status', $statusField->name);
        $this->assertTrue($statusField->filterable);
        $this->assertTrue($statusField->sortable);

        $statusData = $statusField->data;
        $this->assertCount(2, $statusData);
        $this->assertEquals('active', $statusData[0]['value']);
        $this->assertEquals('Active', $statusData[0]['label']);

        // Test group field
        $groupField = $fields[4];
        $this->assertEquals('contact_info', $groupField->name);
        $this->assertEquals('Contact Information', $groupField->display);
        $this->assertEquals(12, $groupField->col);

        // Test group children
        $children = $groupField->children;
        $this->assertCount(2, $children);
        $this->assertEquals('phone', $children[0]->name);
        $this->assertEquals('address', $children[1]->name);
    }

    /**
     * Test field data flow from model to field values
     */
    public function test_field_data_flow_integration()
    {
        $user = new TestUser([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);

        $scaffolder = new class extends BaseScaffolder {
            protected function setFields(): array
            {
                return [
                    Text::make('first_name'),
                    Text::make('last_name'),
                    Text::make('email'),
                    Select::make('status')
                ];
            }

            protected function setModel(): string
            {
                return TestUser::class;
            }

            protected function setModuleName(): string
            {
                return 'user';
            }
        };

        // Initialize scaffolder with model
        $scaffolder->scaffold('edit', $user);

        // Test field value extraction through FieldsHandler
        $fields = $scaffolder->getEditFields();

        foreach ($fields as $fieldData) {
            $fieldName = $fieldData->name;

            // Verify field gets correct value from model
            if (isset($user->$fieldName)) {
                $this->assertEquals($user->$fieldName, $fieldData->value,
                    "Field {$fieldName} should extract correct value from model");
            }
        }
    }

    /**
     * Test field validation rules integration
     */
    public function test_field_validation_rules_integration()
    {
        $scaffolder = new class extends BaseScaffolder {
            protected function setFields(): array
            {
                return [
                    Text::make('email')
                        ->rules('required', 'email', 'unique:users,email'),
                    Text::make('first_name')
                        ->rules('required', 'min:2', 'max:50'),
                    Select::make('status')
                        ->rules('required', 'in:active,inactive')
                ];
            }

            protected function setModel(): string
            {
                return TestUser::class;
            }

            protected function setModuleName(): string
            {
                return 'user';
            }
        };

        $scaffolder->scaffold('create');
        $fields = $scaffolder->getCreateFields();

        // Test email field rules
        $emailField = $fields[0];
        $this->assertContains('required', $emailField->rules);
        $this->assertContains('email', $emailField->rules);
        $this->assertContains('unique:users,email', $emailField->rules);

        // Test first_name field rules
        $firstNameField = $fields[1];
        $this->assertContains('required', $firstNameField->rules);
        $this->assertContains('min:2', $firstNameField->rules);
        $this->assertContains('max:50', $firstNameField->rules);

        // Test status field rules
        $statusField = $fields[2];
        $this->assertContains('required', $statusField->rules);
        $this->assertContains('in:active,inactive', $statusField->rules);
    }

    /**
     * Test field filtering and searching integration
     */
    public function test_field_filtering_and_searching_integration()
    {
        $scaffolder = new class extends BaseScaffolder {
            protected function setFields(): array
            {
                return [
                    Text::make('first_name')->searchable()->filter(),
                    Text::make('last_name')->searchable(),
                    Text::make('email')->searchable()->filter()->sortable(),
                    Select::make('status')->filter()->sortable()
                ];
            }

            protected function setModel(): string
            {
                return TestUser::class;
            }

            protected function setModuleName(): string
            {
                return 'user';
            }
        };

        $scaffolder->scaffold('index');
        $fields = $scaffolder->getIndexFields();

        // Test searchable fields
        $searchableFields = array_filter($fields, fn($field) => $field->searchable);
        $this->assertCount(3, $searchableFields, 'Should have 3 searchable fields');

        // Test filterable fields
        $filterableFields = array_filter($fields, fn($field) => $field->filterable);
        $this->assertCount(3, $filterableFields, 'Should have 3 filterable fields');

        // Test sortable fields
        $sortableFields = array_filter($fields, fn($field) => $field->sortable);
        $this->assertCount(2, $sortableFields, 'Should have 2 sortable fields');

        // Verify specific field configurations
        $emailField = array_find($fields, fn($field) => $field->name === 'email');
        $this->assertTrue($emailField->searchable);
        $this->assertTrue($emailField->filterable);
        $this->assertTrue($emailField->sortable);
    }
}
