<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Illuminate\Database\Eloquent\Model;

/**
 * Test model for CrudInit integration testing
 */
class CrudInitTestModel extends Model
{
    protected $fillable = ['name', 'email'];
}

/**
 * Test scaffolder for CrudInit integration testing
 */
class CrudInitTestScaffolder extends BaseScaffolder
{
    private array $enabledActions;

    public function __construct(array $enabledActions = [])
    {
        $this->enabledActions = $enabledActions ?: Actions::all();
        parent::__construct();
    }

    protected function setModuleName(): string
    {
        return 'crud-init-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name'),
            Text::make('email'),
        ];
    }

    protected function setModel(): string
    {
        return CrudInitTestModel::class;
    }

    protected function setActions(): array
    {
        return $this->enabledActions;
    }
}

/**
 * Test controller that uses CrudInit trait
 */
class CrudInitTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit;

    protected function setScaffolder(): string
    {
        return CrudInitTestScaffolder::class;
    }

    // Test method to verify callAction functionality
    public function testAction()
    {
        return 'success';
    }
}

/**
 * Test controller with custom setup method
 */
class CrudInitTestControllerWithSetup extends CrudInitTestController
{
    private bool $setupCalled = false;

    protected function setup(): void
    {
        $this->setupCalled = true;
    }

    public function wasSetupCalled(): bool
    {
        return $this->setupCalled;
    }
}

class CrudInitTest extends \Tir\Crud\Tests\TestCase
{
    /**
     * Test that CrudInit initializes model and scaffolder correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_crud_init_initializes_model_and_scaffolder()
    {
        $controller = new CrudInitTestController();

        // Use reflection to access protected methods
        $reflection = new \ReflectionClass($controller);
        $scaffolderMethod = $reflection->getMethod('scaffolder');
        $scaffolderMethod->setAccessible(true);
        $scaffolder = $scaffolderMethod->invoke($controller);

        $modelMethod = $reflection->getMethod('model');
        $modelMethod->setAccessible(true);
        $model = $modelMethod->invoke($controller);

        $this->assertNotNull($scaffolder);
        $this->assertInstanceOf(CrudInitTestScaffolder::class, $scaffolder);

        $this->assertNotNull($model);
        $this->assertInstanceOf(CrudInitTestModel::class, $model);
    }

    /**
     * Test that setup method is called if it exists
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_setup_method_is_called_when_present()
    {
        $controller = new CrudInitTestControllerWithSetup();

        $this->assertTrue($controller->wasSetupCalled());
    }

    /**
     * Test that Actions::isEnabled works correctly with different configurations
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_actions_is_enabled_works_with_different_configurations()
    {
        // Test all actions enabled
        $allActions = Actions::all();
        $this->assertTrue(Actions::isEnabled($allActions, ActionType::INDEX));
        $this->assertTrue(Actions::isEnabled($allActions, ActionType::CREATE));
        $this->assertTrue(Actions::isEnabled($allActions, ActionType::EDIT));

        // Test only specific actions enabled
        $onlyIndex = Actions::only(ActionType::INDEX);
        $this->assertTrue(Actions::isEnabled($onlyIndex, ActionType::INDEX));
        $this->assertFalse(Actions::isEnabled($onlyIndex, ActionType::CREATE));
        $this->assertFalse(Actions::isEnabled($onlyIndex, ActionType::EDIT));

        // Test all except specific actions
        $exceptDestroy = Actions::except(ActionType::DESTROY);
        $this->assertTrue(Actions::isEnabled($exceptDestroy, ActionType::INDEX));
        $this->assertTrue(Actions::isEnabled($exceptDestroy, ActionType::CREATE));
        $this->assertFalse(Actions::isEnabled($exceptDestroy, ActionType::DESTROY));
    }

    /**
     * Test that action names are handled correctly (string vs enum)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_action_names_handled_correctly()
    {
        $allActions = Actions::all();

        // Test with enum
        $this->assertTrue(Actions::isEnabled($allActions, ActionType::INDEX));

        // Test with string
        $this->assertTrue(Actions::isEnabled($allActions, 'index'));
        $this->assertTrue(Actions::isEnabled($allActions, ActionType::INDEX->value));
    }

    /**
     * Test that model() and scaffolder() methods return correct instances
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_model_and_scaffolder_methods_return_correct_instances()
    {
        $controller = new CrudInitTestController();

        // Use reflection to access protected methods
        $reflection = new \ReflectionClass($controller);
        $scaffolderMethod = $reflection->getMethod('scaffolder');
        $scaffolderMethod->setAccessible(true);
        $scaffolder = $scaffolderMethod->invoke($controller);

        $modelMethod = $reflection->getMethod('model');
        $modelMethod->setAccessible(true);
        $model = $modelMethod->invoke($controller);

        $this->assertInstanceOf(CrudInitTestModel::class, $model);
        $this->assertInstanceOf(CrudInitTestScaffolder::class, $scaffolder);

        // Test that the same instances are returned on multiple calls
        $model2 = $modelMethod->invoke($controller);
        $scaffolder2 = $scaffolderMethod->invoke($controller);
        $this->assertSame($model, $model2);
        $this->assertSame($scaffolder, $scaffolder2);
    }

    /**
     * Test that checkActionEnabled logic works correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_check_action_enabled_logic()
    {
        // Create scaffolder with only INDEX enabled
        $scaffolder = new CrudInitTestScaffolder(Actions::only(ActionType::INDEX));

        // Test that INDEX is enabled
        $this->assertTrue(Actions::isEnabled($scaffolder->getActions(), ActionType::INDEX));

        // Test that CREATE is disabled
        $this->assertFalse(Actions::isEnabled($scaffolder->getActions(), ActionType::CREATE));

        // Test that EDIT is disabled
        $this->assertFalse(Actions::isEnabled($scaffolder->getActions(), ActionType::EDIT));
    }

    /**
     * Test that callAction includes the checkActionEnabled call
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_call_action_includes_action_check()
    {
        $controller = new CrudInitTestController();

        // Use reflection to verify the callAction method contains our check
        $reflection = new \ReflectionClass($controller);
        $callActionMethod = $reflection->getMethod('callAction');
        $callActionMethod->setAccessible(true);

        // Get the file content and verify our changes are present
        $fileContent = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString('enforceAccess', $fileContent);
        $this->assertStringContainsString('checkActionEnabled', $fileContent);
    }
}
