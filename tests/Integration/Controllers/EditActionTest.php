<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Illuminate\Database\Eloquent\Model;

/**
 * Test model for Edit action integration testing
 */
class EditActionTestModel extends Model
{
    protected $fillable = ['name', 'email'];

    public $timestamps = false; // Disable timestamps for testing
}

/**
 * Test scaffolder for Edit action integration testing
 */
class EditActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'edit-action-test';
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
        return EditActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Edit action integration testing
 */
class EditActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Edit;

    protected function setScaffolder(): string
    {
        return EditActionTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onEditCalled = false;
    private bool $onEditResponseCalled = false;
    private mixed $editModel = null;
    private mixed $responseData = null;

    protected function setup(): void
    {
        // Test the onEdit hook
        $this->onEdit(function ($defaultEdit, $id) {
            $this->onEditCalled = true;
            $this->editModel = $defaultEdit($id);
            // Modify the model for testing (add a test attribute)
            $this->editModel->test_attribute = 'modified_by_hook';
            return $this->editModel;
        });

        // Test the onEditResponse hook
        $this->onEditResponse(function ($defaultResponse, $dataModel) {
            $this->onEditResponseCalled = true;
            $this->responseData = $defaultResponse($dataModel);
            return $this->responseData;
        });
    }

    // Getters for testing hook execution
    public function wasOnEditCalled(): bool
    {
        return $this->onEditCalled;
    }

    public function wasOnEditResponseCalled(): bool
    {
        return $this->onEditResponseCalled;
    }

    public function getEditModel(): mixed
    {
        return $this->editModel;
    }

    public function getResponseData(): mixed
    {
        return $this->responseData;
    }
}

class EditActionTest extends \Tir\Crud\Tests\TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table manually since we don't have migrations for test models
        \Illuminate\Support\Facades\Schema::create('edit_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
        });
    }

    /**
     * Test that edit action returns proper scaffold structure
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_edit_action_returns_proper_scaffold_structure()
    {
        // Create a test record
        $testRecord = EditActionTestModel::create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $controller = new EditActionTestController();

        // Call the edit action
        $response = $controller->edit($testRecord->id);
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('buttons', $data);
        $this->assertArrayHasKey('validationMsg', $data);
        $this->assertArrayHasKey('configs', $data);

        // Assert fields structure
        $this->assertIsArray($data['fields']);
        $this->assertCount(2, $data['fields']); // 2 fields defined in scaffolder

        // Assert buttons structure
        $this->assertIsArray($data['buttons']);

        // Assert configs structure
        $this->assertIsArray($data['configs']);
        $this->assertArrayHasKey('actions', $data['configs']);
    }

    /**
     * Test that edit action includes access-filtered actions in config
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_edit_action_includes_access_filtered_actions()
    {
        // Create a test record
        $testRecord = EditActionTestModel::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        $controller = new EditActionTestController();

        $response = $controller->edit($testRecord->id);
        $data = $response->getData(true);

        // Assert that actions are included in configs
        $this->assertArrayHasKey('actions', $data['configs']);
        $this->assertIsArray($data['configs']['actions']);

        // Since all actions are enabled in our test scaffolder,
        // all actions should be available (subject to access control)
        $this->assertArrayHasKey(ActionType::INDEX->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::EDIT->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::SHOW->value, $data['configs']['actions']);
    }

    /**
     * Test that onEdit hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_edit_hook_is_executed()
    {
        // Create a test record
        $testRecord = EditActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new EditActionTestController();

        $controller->edit($testRecord->id);

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnEditCalled());
        $this->assertNotNull($controller->getEditModel());

        // Assert that the hook modified the model
        $this->assertEquals('modified_by_hook', $controller->getEditModel()->test_attribute);
    }

    /**
     * Test that onEditResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_edit_response_hook_is_executed()
    {
        // Create a test record
        $testRecord = EditActionTestModel::create([
            'name' => 'Response Test',
            'email' => 'response@example.com'
        ]);

        $controller = new EditActionTestController();

        $controller->edit($testRecord->id);

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnEditResponseCalled());
        $this->assertNotNull($controller->getResponseData());
    }

    /**
     * Test that edit action returns JSON response with correct status
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_edit_action_returns_json_response_with_correct_status()
    {
        // Create a test record
        $testRecord = EditActionTestModel::create([
            'name' => 'Status Test',
            'email' => 'status@example.com'
        ]);

        $controller = new EditActionTestController();

        $response = $controller->edit($testRecord->id);

        // Assert response type and status
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that edit action throws exception for non-existent record
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_edit_action_throws_exception_for_non_existent_record()
    {
        $controller = new EditActionTestController();

        // Try to edit a non-existent record
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $controller->edit(999);
    }

    /**
     * Test that edit action works with disabled edit action (should throw exception)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_edit_action_with_disabled_edit_action_throws_exception()
    {
        // Disable access control globally for this test
        config()->set('crud.accessLevelControl', 'off');

        // Create a test record
        $testRecord = EditActionTestModel::create([
            'name' => 'Disabled Test',
            'email' => 'disabled@example.com'
        ]);

        // Create a controller with edit action disabled
        $controller = new EditActionTestController();
        $controllerRef = new \ReflectionClass($controller);

        // Override the scaffolder to return disabled edit action
        $scaffolderProp = $controllerRef->getProperty('scaffolder');
        $scaffolderProp->setAccessible(true);

        $disabledScaffolder = new class extends EditActionTestScaffolder {
            protected function setActions(): array
            {
                return \Tir\Crud\Support\Scaffold\Actions::except(\Tir\Crud\Support\Enums\ActionType::EDIT);
            }
        };

        $scaffolderProp->setValue($controller, $disabledScaffolder);

        // Use callAction to trigger access control checks
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $controller->callAction('edit', [$testRecord->id]);
    }
}
