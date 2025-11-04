<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Controllers\CrudController;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test model for Create action integration testing
 */
class CreateActionTestModel extends Model
{
    protected $fillable = ['name', 'email'];
}

/**
 * Test scaffolder for Create action integration testing
 */
class CreateActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'create-action-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules('required|string|max:255'),
            Text::make('email')->rules('required|email'),
        ];
    }

    protected function setModel(): string
    {
        return CreateActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Create action integration testing
 */
class CreateActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Create;

    protected function setScaffolder(): string
    {
        return CreateActionTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onCreateCalled = false;
    private bool $onCreateResponseCalled = false;
    private mixed $createFields = null;
    private mixed $responseData = null;

    protected function setup(): void
    {
        // Test the onCreate hook
        $this->onCreate(function ($defaultCreate) {
            $this->onCreateCalled = true;
            $this->createFields = $defaultCreate();
            // Modify the fields for testing
            $this->createFields['fields'][] = [
                'name' => 'test_field',
                'type' => 'text',
                'display' => 'Test Field'
            ];
            return $this->createFields;
        });

        // Test the onCreateResponse hook
        $this->onCreateResponse(function ($defaultResponse, $fields) {
            $this->onCreateResponseCalled = true;
            $this->responseData = $fields;
            return $defaultResponse($fields);
        });
    }

    // Getters for testing hook execution
    public function wasOnCreateCalled(): bool
    {
        return $this->onCreateCalled;
    }

    public function wasOnCreateResponseCalled(): bool
    {
        return $this->onCreateResponseCalled;
    }

    public function getCreateFields(): mixed
    {
        return $this->createFields;
    }

    public function getResponseData(): mixed
    {
        return $this->responseData;
    }
}

class CreateActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    /**
     * Test that create action returns proper scaffold structure
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_action_returns_proper_scaffold_structure()
    {
        $controller = new CreateActionTestController();

        // Call the create action
        $response = $controller->create();
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('buttons', $data);
        $this->assertArrayHasKey('validationMsg', $data);
        $this->assertArrayHasKey('configs', $data);

        // Assert fields structure
        $this->assertIsArray($data['fields']);
        $this->assertCount(3, $data['fields']); // 2 original + 1 added by hook

        // Check that our test field was added by the hook
        $fieldNames = array_column($data['fields'], 'name');
        $this->assertContains('test_field', $fieldNames);

        // Assert buttons structure
        $this->assertIsArray($data['buttons']);

        // Assert configs structure
        $this->assertIsArray($data['configs']);
        $this->assertArrayHasKey('actions', $data['configs']);
    }

    /**
     * Test that create action includes access-filtered actions in config
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_action_includes_access_filtered_actions()
    {
        $controller = new CreateActionTestController();

        $response = $controller->create();
        $data = $response->getData(true);

        // Assert that actions are included in configs
        $this->assertArrayHasKey('actions', $data['configs']);
        $this->assertIsArray($data['configs']['actions']);

        // Since all actions are enabled in our test scaffolder,
        // all actions should be available (subject to access control)
        $this->assertArrayHasKey(ActionType::INDEX->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::CREATE->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::SHOW->value, $data['configs']['actions']);
    }

    /**
     * Test that onCreate hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_create_hook_is_executed()
    {
        $controller = new CreateActionTestController();

        $controller->create();

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnCreateCalled());
        $this->assertNotNull($controller->getCreateFields());
    }

    /**
     * Test that onCreateResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_create_response_hook_is_executed()
    {
        $controller = new CreateActionTestController();

        $controller->create();

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnCreateResponseCalled());
        $this->assertNotNull($controller->getResponseData());
    }

    /**
     * Test that create action returns JSON response with correct status
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_action_returns_json_response_with_correct_status()
    {
        $controller = new CreateActionTestController();

        $response = $controller->create();

        // Assert response type and status
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that create action fields include validation rules
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_action_fields_include_validation_rules()
    {
        $controller = new CreateActionTestController();

        $response = $controller->create();
        $data = $response->getData(true);

        // Find the name field
        $nameField = null;
        foreach ($data['fields'] as $field) {
            if ($field['name'] === 'name') {
                $nameField = $field;
                break;
            }
        }

        $this->assertNotNull($nameField);
        $this->assertArrayHasKey('rules', $nameField);
        $this->assertNotEmpty($nameField['rules']);
    }

    /**
     * Test that create action works with disabled create action (should throw exception)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_action_with_disabled_create_action_throws_exception()
    {
        // Disable access control globally for this test
        config()->set('crud.accessLevelControl', 'off');

        // Create a controller with create action disabled
        $controller = new CreateActionTestController();
        $controllerRef = new \ReflectionClass($controller);

        // Override the scaffolder to return disabled create action
        $scaffolderProp = $controllerRef->getProperty('scaffolder');
        $scaffolderProp->setAccessible(true);

        $disabledScaffolder = new class extends CreateActionTestScaffolder {
            protected function setActions(): array
            {
                return \Tir\Crud\Support\Scaffold\Actions::except(\Tir\Crud\Support\Enums\ActionType::CREATE);
            }
        };

        $scaffolderProp->setValue($controller, $disabledScaffolder);

        // Use callAction to trigger access control checks
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $controller->callAction('create', []);
    }
}
