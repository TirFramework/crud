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
 * Test model for Destroy/Restore action integration testing
 */
class DestroyRestoreActionTestModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email'];
}

/**
 * Test scaffolder for Destroy/Restore action integration testing
 */
class DestroyRestoreActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'destroy-restore-action-test';
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
        return DestroyRestoreActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Destroy/Restore action integration testing
 */
class DestroyRestoreActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Destroy,
        \Tir\Crud\Controllers\Traits\Restore;

    protected function setScaffolder(): string
    {
        return DestroyRestoreActionTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onDestroyCalled = false;
    private bool $onDestroyResponseCalled = false;
    private bool $onRestoreCalled = false;
    private bool $onRestoreResponseCalled = false;
    private mixed $destroyedItem = null;
    private mixed $restoredItem = null;
    private mixed $destroyResponseData = null;
    private mixed $restoreResponseData = null;

    protected function setup(): void
    {
        // Test the onDestroy hook
        $this->onDestroy(function ($defaultDestroy) {
            $this->onDestroyCalled = true;
            $this->destroyedItem = $defaultDestroy();
            return $this->destroyedItem;
        });

        // Test the onDestroyResponse hook
        $this->onDestroyResponse(function ($defaultResponse, $item) {
            $this->onDestroyResponseCalled = true;
            $this->destroyResponseData = $item;
            return $defaultResponse($item);
        });

        // Test the onRestore hook
        $this->onRestore(function ($defaultRestore) {
            $this->onRestoreCalled = true;
            $this->restoredItem = $defaultRestore();
            return $this->restoredItem;
        });

        // Test the onRestoreResponse hook
        $this->onRestoreResponse(function ($defaultResponse, $item) {
            $this->onRestoreResponseCalled = true;
            $this->restoreResponseData = $item;
            return $defaultResponse($item);
        });
    }

    // Getters for testing hook execution
    public function wasOnDestroyCalled(): bool
    {
        return $this->onDestroyCalled;
    }

    public function wasOnDestroyResponseCalled(): bool
    {
        return $this->onDestroyResponseCalled;
    }

    public function wasOnRestoreCalled(): bool
    {
        return $this->onRestoreCalled;
    }

    public function wasOnRestoreResponseCalled(): bool
    {
        return $this->onRestoreResponseCalled;
    }

    public function getDestroyedItem(): mixed
    {
        return $this->destroyedItem;
    }

    public function getRestoredItem(): mixed
    {
        return $this->restoredItem;
    }

    public function getDestroyResponseData(): mixed
    {
        return $this->destroyResponseData;
    }

    public function getRestoreResponseData(): mixed
    {
        return $this->restoreResponseData;
    }
}

class DestroyRestoreActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('destroy_restore_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Test that destroy action soft deletes a model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_destroy_action_soft_deletes_model()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new DestroyRestoreActionTestController();

        // Call the destroy action
        $response = $controller->destroy($model->id);
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('deleted', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['deleted']);
        $this->assertIsString($data['message']);

        // Assert the model was soft deleted
        $model->refresh();
        $this->assertTrue($model->trashed());
        $this->assertNotNull($model->deleted_at);
    }

    /**
     * Test that restore action restores a soft deleted model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_restore_action_restores_soft_deleted_model()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Soft delete the model first
        $model->delete();
        $this->assertTrue($model->trashed());

        $controller = new DestroyRestoreActionTestController();

        // Call the restore action
        $response = $controller->restore($model->id);
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('restored', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['restored']);
        $this->assertIsString($data['message']);

        // Assert the model was restored
        $model->refresh();
        $this->assertFalse($model->trashed());
        $this->assertNull($model->deleted_at);
    }

    /**
     * Test that destroy action throws exception for non-existent model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_destroy_action_throws_exception_for_non_existent_model()
    {
        $controller = new DestroyRestoreActionTestController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->destroy(999); // Non-existent ID
    }

    /**
     * Test that restore action throws exception for non-existent trashed model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_restore_action_throws_exception_for_non_existent_trashed_model()
    {
        $controller = new DestroyRestoreActionTestController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->restore(999); // Non-existent ID in trash
    }

    /**
     * Test that restore action throws exception for active (non-trashed) model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_restore_action_throws_exception_for_active_model()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com'
        ]);

        $controller = new DestroyRestoreActionTestController();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $controller->restore($model->id); // Active model, not in trash
    }

    /**
     * Test that onDestroy hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_destroy_hook_is_executed()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new DestroyRestoreActionTestController();

        $controller->destroy($model->id);

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnDestroyCalled());
        $this->assertNotNull($controller->getDestroyedItem());
        $this->assertEquals($model->id, $controller->getDestroyedItem()->id);
    }

    /**
     * Test that onDestroyResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_destroy_response_hook_is_executed()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $controller = new DestroyRestoreActionTestController();

        $controller->destroy($model->id);

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnDestroyResponseCalled());
        $this->assertNotNull($controller->getDestroyResponseData());
    }

    /**
     * Test that onRestore hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_restore_hook_is_executed()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Soft delete first
        $model->delete();

        $controller = new DestroyRestoreActionTestController();

        $controller->restore($model->id);

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnRestoreCalled());
        $this->assertNotNull($controller->getRestoredItem());
        $this->assertEquals($model->id, $controller->getRestoredItem()->id);
    }

    /**
     * Test that onRestoreResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_restore_response_hook_is_executed()
    {
        $model = DestroyRestoreActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        // Soft delete first
        $model->delete();

        $controller = new DestroyRestoreActionTestController();

        $controller->restore($model->id);

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnRestoreResponseCalled());
        $this->assertNotNull($controller->getRestoreResponseData());
    }
}
