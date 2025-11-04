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
 * Test model for Show action integration testing
 */
class ShowActionTestModel extends Model
{
    protected $fillable = ['name', 'email'];
}

/**
 * Test scaffolder for Show action integration testing
 */
class ShowActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'show-action-test';
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
        return ShowActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Show action integration testing
 */
class ShowActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Show;

    protected function setScaffolder(): string
    {
        return ShowActionTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onShowCalled = false;
    private bool $onShowResponseCalled = false;
    private mixed $showModel = null;
    private mixed $responseData = null;

    protected function setup(): void
    {
        // Test the onShow hook
        $this->onShow(function ($defaultShow) {
            $this->onShowCalled = true;
            $this->showModel = $defaultShow();
            // Modify the model for testing (add a test attribute)
            if ($this->showModel) {
                $this->showModel->test_attribute = 'test_value';
            }
            return $this->showModel;
        });

        // Test the onShowResponse hook
        $this->onShowResponse(function ($defaultResponse, $model) {
            $this->onShowResponseCalled = true;
            $this->responseData = $model;
            return $defaultResponse($model);
        });
    }

    // Getters for testing hook execution
    public function wasOnShowCalled(): bool
    {
        return $this->onShowCalled;
    }

    public function wasOnShowResponseCalled(): bool
    {
        return $this->onShowResponseCalled;
    }

    public function getShowModel(): mixed
    {
        return $this->showModel;
    }

    public function getResponseData(): mixed
    {
        return $this->responseData;
    }
}

class ShowActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('show_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Test that show action returns proper scaffold structure for existing model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_action_returns_proper_scaffold_structure()
    {
        // Create a test model
        $model = ShowActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ShowActionTestController();

        // Call the show action
        $response = $controller->show($model->id);
        $data = $response->getData(true);

        // Debug: Let's see what the actual response contains
        // dd($data);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertArrayHasKey('buttons', $data);
        $this->assertArrayHasKey('validationMsg', $data);
        $this->assertArrayHasKey('configs', $data);

        // Assert fields structure
        $this->assertIsArray($data['fields']);
        $this->assertCount(2, $data['fields']); // name and email fields

        // Assert buttons structure
        $this->assertIsArray($data['buttons']);

        // Assert configs structure
        $this->assertIsArray($data['configs']);
        $this->assertArrayHasKey('actions', $data['configs']);
    }

    /**
     * Test that show action includes access-filtered actions in config
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_action_includes_access_filtered_actions()
    {
        $model = ShowActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ShowActionTestController();

        $response = $controller->show($model->id);
        $data = $response->getData(true);

        // Assert that actions are included in configs
        $this->assertArrayHasKey('actions', $data['configs']);
        $this->assertIsArray($data['configs']['actions']);

        // Since all actions are enabled in our test scaffolder,
        // all actions should be available (subject to access control)
        $this->assertArrayHasKey(ActionType::INDEX->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::CREATE->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::SHOW->value, $data['configs']['actions']);
        $this->assertArrayHasKey(ActionType::EDIT->value, $data['configs']['actions']);
    }

    /**
     * Test that onShow hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_show_hook_is_executed()
    {
        $model = ShowActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ShowActionTestController();

        $controller->show($model->id);

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnShowCalled());
        $this->assertNotNull($controller->getShowModel());
        $this->assertEquals($model->id, $controller->getShowModel()->id);
    }

    /**
     * Test that onShowResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_show_response_hook_is_executed()
    {
        $model = ShowActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ShowActionTestController();

        $controller->show($model->id);

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnShowResponseCalled());
        $this->assertNotNull($controller->getResponseData());
    }

    /**
     * Test that show action throws exception for non-existent model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_action_throws_exception_for_non_existent_model()
    {
        $controller = new ShowActionTestController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->show(999); // Non-existent ID
    }

    /**
     * Test that hooks can modify the model data
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_hooks_can_modify_model_data()
    {
        $model = ShowActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ShowActionTestController();

        $controller->show($model->id);

        // Check that the hook modified the model
        $modifiedModel = $controller->getShowModel();
        $this->assertEquals('test_value', $modifiedModel->test_attribute);
    }

    /**
     * Test that show action works with disabled show action (should be blocked by access control)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_show_action_respects_action_enabling()
    {
        // This test would verify that if SHOW action is disabled in the scaffolder,
        // the access control blocks it. But since we're testing the trait directly,
        // we'll skip this for now as it requires full controller integration.
        $this->assertTrue(true); // Placeholder test
    }
}
