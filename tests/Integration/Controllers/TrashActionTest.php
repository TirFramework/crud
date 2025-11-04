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
 * Test model for Trash action integration testing
 */
class TrashActionTestModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email'];
}

/**
 * Test scaffolder for Trash action integration testing
 */
class TrashActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'trash-action-test';
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
        return TrashActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Trash action integration testing
 */
class TrashActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Trash;

    protected function setScaffolder(): string
    {
        return TrashActionTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onInitQueryCalled = false;
    private bool $onTrashResponseCalled = false;
    private bool $onSelectCalled = false;
    private bool $onFilterCalled = false;
    private mixed $queryResult = null;
    private mixed $responseData = null;

    protected function setup(): void
    {
        // Test the onInitQuery hook (called by DataService for trash)
        $this->onInitQuery(function ($defaultInitQuery) {
            $this->onInitQueryCalled = true;
            $this->queryResult = $defaultInitQuery();
            return $this->queryResult;
        });

        // Test the onTrashResponse hook
        $this->onTrashResponse(function ($defaultResponse, $items) {
            $this->onTrashResponseCalled = true;
            $this->responseData = $items;
            return $defaultResponse($items);
        });

        // Test the onSelect hook
        $this->onSelect(function ($defaultSelect, $query) {
            $this->onSelectCalled = true;
            return $defaultSelect($query);
        });

        // Test the onFilter hook
        $this->onFilter(function ($defaultFilter, $query) {
            $this->onFilterCalled = true;
            return $defaultFilter($query);
        });
    }

    // Getters for testing hook execution
    public function wasOnInitQueryCalled(): bool
    {
        return $this->onInitQueryCalled;
    }

    public function wasOnTrashResponseCalled(): bool
    {
        return $this->onTrashResponseCalled;
    }

    public function wasOnSelectCalled(): bool
    {
        return $this->onSelectCalled;
    }

    public function wasOnFilterCalled(): bool
    {
        return $this->onFilterCalled;
    }

    public function getQueryResult(): mixed
    {
        return $this->queryResult;
    }

    public function getResponseData(): mixed
    {
        return $this->responseData;
    }
}

class TrashActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('trash_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Test that trash action returns only soft-deleted models
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_returns_only_soft_deleted_models()
    {
        // Create some models
        $activeModel = TrashActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com'
        ]);

        $trashedModel1 = TrashActionTestModel::create([
            'name' => 'Trashed User 1',
            'email' => 'trashed1@example.com'
        ]);
        $trashedModel1->delete();

        $trashedModel2 = TrashActionTestModel::create([
            'name' => 'Trashed User 2',
            'email' => 'trashed2@example.com'
        ]);
        $trashedModel2->delete();

        $controller = new TrashActionTestController();

        // Call the trash action
        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);

        // Assert that only trashed models are returned
        $this->assertCount(2, $data['data']);

        $names = array_column($data['data'], 'name');
        $this->assertContains('Trashed User 1', $names);
        $this->assertContains('Trashed User 2', $names);
        $this->assertNotContains('Active User', $names);
    }

    /**
     * Test that trash action returns empty result when no trashed models exist
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_returns_empty_when_no_trashed_models()
    {
        // Create only active models
        TrashActionTestModel::create([
            'name' => 'Active User 1',
            'email' => 'active1@example.com'
        ]);

        TrashActionTestModel::create([
            'name' => 'Active User 2',
            'email' => 'active2@example.com'
        ]);

        $controller = new TrashActionTestController();

        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert empty result
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that onInitQuery hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_init_query_hook_is_executed()
    {
        $controller = new TrashActionTestController();

        $controller->trashData();

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnInitQueryCalled());
        $this->assertNotNull($controller->getQueryResult());
    }

    /**
     * Test that onTrashResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_trash_response_hook_is_executed()
    {
        $controller = new TrashActionTestController();

        $controller->trashData();

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnTrashResponseCalled());
        $this->assertNotNull($controller->getResponseData());
    }

    /**
     * Test that onSelect hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_select_hook_is_executed()
    {
        $controller = new TrashActionTestController();

        $controller->trashData();

        // Assert that the select hook was called
        $this->assertTrue($controller->wasOnSelectCalled());
    }

    /**
     * Test that onFilter hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_filter_hook_is_executed()
    {
        $controller = new TrashActionTestController();

        $controller->trashData();

        // Assert that the filter hook was called
        $this->assertTrue($controller->wasOnFilterCalled());
    }

    /**
     * Test that trash action includes pagination metadata
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_includes_pagination_metadata()
    {
        // Create multiple trashed models to test pagination
        for ($i = 1; $i <= 5; $i++) {
            $model = TrashActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com"
            ]);
            $model->delete();
        }

        $controller = new TrashActionTestController();

        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert pagination structure
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('last_page', $data);
        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);

        // Assert pagination values
        $this->assertEquals(1, $data['current_page']);
        $this->assertEquals(5, $data['total']);
        $this->assertCount(5, $data['data']);
    }
}
