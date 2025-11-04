<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Controllers\CrudController;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test model for ForceDelete action integration testing
 */
class ForceDeleteActionTestModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email'];
}

/**
 * Test scaffolder for ForceDelete action integration testing
 */
class ForceDeleteActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'force-delete-action-test';
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
        return ForceDeleteActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for ForceDelete action integration testing
 */
class ForceDeleteActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\ForceDelete;

    protected function setScaffolder(): string
    {
        return ForceDeleteActionTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onForceDeleteCalled = false;
    private bool $onForceDeleteResponseCalled = false;
    private bool $onEmptyTrashCalled = false;
    private bool $onEmptyTrashResponseCalled = false;
    private mixed $forceDeletedItem = null;
    private mixed $emptyTrashCount = null;
    private mixed $forceDeleteResponseData = null;
    private mixed $emptyTrashResponseData = null;

    protected function setup(): void
    {
        // Test the onForceDelete hook
        $this->onForceDelete(function ($defaultForceDelete) {
            $this->onForceDeleteCalled = true;
            $this->forceDeletedItem = $defaultForceDelete();
            return $this->forceDeletedItem;
        });

        // Test the onForceDeleteResponse hook
        $this->onForceDeleteResponse(function ($defaultResponse, $item) {
            $this->onForceDeleteResponseCalled = true;
            $this->forceDeleteResponseData = $item;
            return $defaultResponse($item);
        });

        // Test the onEmptyTrash hook
        $this->onEmptyTrash(function ($defaultEmptyTrash) {
            $this->onEmptyTrashCalled = true;
            $this->emptyTrashCount = $defaultEmptyTrash();
            return $this->emptyTrashCount;
        });

        // Test the onEmptyTrashResponse hook
        $this->onEmptyTrashResponse(function ($defaultResponse, $count) {
            $this->onEmptyTrashResponseCalled = true;
            $this->emptyTrashResponseData = $count;
            return $defaultResponse($count);
        });
    }

    // Getters for testing hook execution
    public function wasOnForceDeleteCalled(): bool
    {
        return $this->onForceDeleteCalled;
    }

    public function wasOnForceDeleteResponseCalled(): bool
    {
        return $this->onForceDeleteResponseCalled;
    }

    public function wasOnEmptyTrashCalled(): bool
    {
        return $this->onEmptyTrashCalled;
    }

    public function wasOnEmptyTrashResponseCalled(): bool
    {
        return $this->onEmptyTrashResponseCalled;
    }

    public function getForceDeletedItem(): mixed
    {
        return $this->forceDeletedItem;
    }

    public function getEmptyTrashCount(): mixed
    {
        return $this->emptyTrashCount;
    }

    public function getForceDeleteResponseData(): mixed
    {
        return $this->forceDeleteResponseData;
    }

    public function getEmptyTrashResponseData(): mixed
    {
        return $this->emptyTrashResponseData;
    }
}

class ForceDeleteActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('force_delete_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Test that forceDelete action permanently deletes an active model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_force_delete_action_permanently_deletes_active_model()
    {
        $model = ForceDeleteActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com'
        ]);

        $controller = new ForceDeleteActionTestController();

        // Call the forceDelete action
        $response = $controller->forceDelete($model->id);
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('permanently_deleted', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['permanently_deleted']);
        $this->assertIsString($data['message']);

        // Assert the model was permanently deleted
        $this->assertModelMissing($model);
        $this->assertDatabaseMissing('force_delete_action_test_models', ['id' => $model->id]);
    }

    /**
     * Test that forceDelete action permanently deletes a soft-deleted model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_force_delete_action_permanently_deletes_soft_deleted_model()
    {
        $model = ForceDeleteActionTestModel::create([
            'name' => 'Trashed User',
            'email' => 'trashed@example.com'
        ]);

        // Soft delete first
        $model->delete();
        $this->assertTrue($model->trashed());

        $controller = new ForceDeleteActionTestController();

        // Call the forceDelete action on the trashed model
        $response = $controller->forceDelete($model->id);
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('permanently_deleted', $data);
        $this->assertTrue($data['permanently_deleted']);

        // Assert the model was permanently deleted
        $this->assertModelMissing($model);
        $this->assertDatabaseMissing('force_delete_action_test_models', ['id' => $model->id]);
    }

    /**
     * Test that emptyTrash action permanently deletes all soft-deleted models
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_empty_trash_action_permanently_deletes_all_soft_deleted_models()
    {
        // Create multiple models
        $activeModel = ForceDeleteActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com'
        ]);

        $trashedModel1 = ForceDeleteActionTestModel::create([
            'name' => 'Trashed User 1',
            'email' => 'trashed1@example.com'
        ]);
        $trashedModel1->delete();

        $trashedModel2 = ForceDeleteActionTestModel::create([
            'name' => 'Trashed User 2',
            'email' => 'trashed2@example.com'
        ]);
        $trashedModel2->delete();

        $controller = new ForceDeleteActionTestController();

        // Call the emptyTrash action
        $response = $controller->emptyTrash();
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('trash_emptied', $data);
        $this->assertArrayHasKey('deleted_count', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['trash_emptied']);
        $this->assertEquals(2, $data['deleted_count']);
        $this->assertIsString($data['message']);

        // Assert trashed models were permanently deleted
        $this->assertModelMissing($trashedModel1);
        $this->assertModelMissing($trashedModel2);
        $this->assertDatabaseMissing('force_delete_action_test_models', ['id' => $trashedModel1->id]);
        $this->assertDatabaseMissing('force_delete_action_test_models', ['id' => $trashedModel2->id]);

        // Assert active model still exists
        $this->assertModelExists($activeModel);
    }

    /**
     * Test that emptyTrash returns zero count when no trashed models exist
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_empty_trash_returns_zero_when_no_trashed_models()
    {
        // Create only active models
        ForceDeleteActionTestModel::create([
            'name' => 'Active User 1',
            'email' => 'active1@example.com'
        ]);

        ForceDeleteActionTestModel::create([
            'name' => 'Active User 2',
            'email' => 'active2@example.com'
        ]);

        $controller = new ForceDeleteActionTestController();

        $response = $controller->emptyTrash();
        $data = $response->getData(true);

        // Assert zero deleted count
        $this->assertEquals(0, $data['deleted_count']);
        $this->assertTrue($data['trash_emptied']);
    }

    /**
     * Test that forceDelete action throws exception for non-existent model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_force_delete_action_throws_exception_for_non_existent_model()
    {
        $controller = new ForceDeleteActionTestController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->forceDelete(999); // Non-existent ID
    }

    /**
     * Test that onForceDelete hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_force_delete_hook_is_executed()
    {
        $model = ForceDeleteActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ForceDeleteActionTestController();

        $controller->forceDelete($model->id);

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnForceDeleteCalled());
        $this->assertNotNull($controller->getForceDeletedItem());
        $this->assertEquals($model->id, $controller->getForceDeletedItem()->id);
    }

    /**
     * Test that onForceDeleteResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_force_delete_response_hook_is_executed()
    {
        $model = ForceDeleteActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new ForceDeleteActionTestController();

        $controller->forceDelete($model->id);

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnForceDeleteResponseCalled());
        $this->assertNotNull($controller->getForceDeleteResponseData());
    }

    /**
     * Test that onEmptyTrash hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_empty_trash_hook_is_executed()
    {
        // Create and trash a model
        $model = ForceDeleteActionTestModel::create([
            'name' => 'Trashed User',
            'email' => 'trashed@example.com'
        ]);
        $model->delete();

        $controller = new ForceDeleteActionTestController();

        $controller->emptyTrash();

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnEmptyTrashCalled());
        $this->assertEquals(1, $controller->getEmptyTrashCount());
    }

    /**
     * Test that onEmptyTrashResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_empty_trash_response_hook_is_executed()
    {
        // Create and trash a model
        $model = ForceDeleteActionTestModel::create([
            'name' => 'Trashed User',
            'email' => 'trashed@example.com'
        ]);
        $model->delete();

        $controller = new ForceDeleteActionTestController();

        $controller->emptyTrash();

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnEmptyTrashResponseCalled());
        $this->assertEquals(1, $controller->getEmptyTrashResponseData());
    }
}
