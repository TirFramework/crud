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
 * Test model for Data action integration testing
 */
class DataActionTestModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'status'];
}

/**
 * Test scaffolder for Data action integration testing
 */
class DataActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'data-action-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules('required|string|max:255'),
            Text::make('email')->rules('required|email'),
            Text::make('status')->rules('required|string'),
        ];
    }

    protected function setModel(): string
    {
        return DataActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for Data action integration testing
 */
class DataActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Data,
        \Tir\Crud\Controllers\Traits\Trash;

    protected function setScaffolder(): string
    {
        return DataActionTestScaffolder::class;
    }

    // Track hook calls for testing data() action
    private bool $onInitQueryCalledForData = false;
    private bool $onIndexResponseCalled = false;
    private bool $onSelectCalledForData = false;
    private bool $onFilterCalledForData = false;
    private mixed $queryResultForData = null;
    private mixed $responseDataForData = null;

    // Track hook calls for testing trashData() action
    private bool $onInitQueryCalledForTrash = false;
    private bool $onTrashResponseCalled = false;
    private bool $onSelectCalledForTrash = false;
    private bool $onFilterCalledForTrash = false;
    private mixed $queryResultForTrash = null;
    private mixed $responseDataForTrash = null;

    protected function setup(): void
    {
        // Hooks for data() action
        $this->onInitQuery(function ($defaultInitQuery) {
            $this->onInitQueryCalledForData = true;
            $this->queryResultForData = $defaultInitQuery();
            return $this->queryResultForData;
        });

        $this->onIndexResponse(function ($defaultResponse, $items) {
            $this->onIndexResponseCalled = true;
            $this->responseDataForData = $items;
            return $defaultResponse($items);
        });

        $this->onSelect(function ($defaultSelect, $query) {
            $this->onSelectCalledForData = true;
            return $defaultSelect($query);
        });

        $this->onFilter(function ($defaultFilter, $query) {
            $this->onFilterCalledForData = true;
            return $defaultFilter($query);
        });

        // Hooks for trashData() action
        $this->onTrashResponse(function ($defaultResponse, $items) {
            $this->onTrashResponseCalled = true;
            $this->responseDataForTrash = $items;
            return $defaultResponse($items);
        });
    }

    // Getters for data() action hooks
    public function wasOnInitQueryCalledForData(): bool
    {
        return $this->onInitQueryCalledForData;
    }

    public function wasOnIndexResponseCalled(): bool
    {
        return $this->onIndexResponseCalled;
    }

    public function wasOnSelectCalledForData(): bool
    {
        return $this->onSelectCalledForData;
    }

    public function wasOnFilterCalledForData(): bool
    {
        return $this->onFilterCalledForData;
    }

    public function getQueryResultForData(): mixed
    {
        return $this->queryResultForData;
    }

    public function getResponseDataForData(): mixed
    {
        return $this->responseDataForData;
    }

    // Getters for trashData() action hooks
    public function wasOnInitQueryCalledForTrash(): bool
    {
        return $this->onInitQueryCalledForTrash;
    }

    public function wasOnTrashResponseCalled(): bool
    {
        return $this->onTrashResponseCalled;
    }

    public function getResponseDataForTrash(): mixed
    {
        return $this->responseDataForTrash;
    }
}

class DataActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    // ============== Tests for data() action ==============

    /**
     * Test that data action returns all active models
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_returns_all_active_models()
    {
        // Create some active models
        $model1 = DataActionTestModel::create([
            'name' => 'Active User 1',
            'email' => 'active1@example.com',
            'status' => 'active'
        ]);

        $model2 = DataActionTestModel::create([
            'name' => 'Active User 2',
            'email' => 'active2@example.com',
            'status' => 'active'
        ]);

        // Create a trashed model (should not appear)
        $trashedModel = DataActionTestModel::create([
            'name' => 'Trashed User',
            'email' => 'trashed@example.com',
            'status' => 'inactive'
        ]);
        $trashedModel->delete();

        $controller = new DataActionTestController();

        // Call the data action
        $response = $controller->data();
        $data = $response->getData(true);

        // Assert the response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('current_page', $data);
        $this->assertArrayHasKey('per_page', $data);

        // Assert that only active models are returned
        $this->assertCount(2, $data['data']);

        $names = array_column($data['data'], 'name');
        $this->assertContains('Active User 1', $names);
        $this->assertContains('Active User 2', $names);
        $this->assertNotContains('Trashed User', $names);
    }

    /**
     * Test that data action returns empty when no active models exist
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_returns_empty_when_no_active_models()
    {
        // Create only trashed models
        $model1 = DataActionTestModel::create([
            'name' => 'Trashed User 1',
            'email' => 'trashed1@example.com',
            'status' => 'inactive'
        ]);
        $model1->delete();

        $model2 = DataActionTestModel::create([
            'name' => 'Trashed User 2',
            'email' => 'trashed2@example.com',
            'status' => 'inactive'
        ]);
        $model2->delete();

        $controller = new DataActionTestController();

        $response = $controller->data();
        $data = $response->getData(true);

        // Assert empty result
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that onInitQuery hook is executed for data action
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_init_query_hook_is_executed_for_data_action()
    {
        $controller = new DataActionTestController();

        $controller->data();

        // Assert that the hook was called
        $this->assertTrue($controller->wasOnInitQueryCalledForData());
        $this->assertNotNull($controller->getQueryResultForData());
    }

    /**
     * Test that onIndexResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_index_response_hook_is_executed()
    {
        $controller = new DataActionTestController();

        $controller->data();

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnIndexResponseCalled());
        $this->assertNotNull($controller->getResponseDataForData());
    }

    /**
     * Test that onSelect hook is executed for data action
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_select_hook_is_executed_for_data_action()
    {
        $controller = new DataActionTestController();

        $controller->data();

        // Assert that the select hook was called
        $this->assertTrue($controller->wasOnSelectCalledForData());
    }

    /**
     * Test that onFilter hook is executed for data action
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_filter_hook_is_executed_for_data_action()
    {
        $controller = new DataActionTestController();

        $controller->data();

        // Assert that the filter hook was called
        $this->assertTrue($controller->wasOnFilterCalledForData());
    }

    /**
     * Test that data action includes pagination metadata
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_includes_pagination_metadata()
    {
        // Create multiple active models
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "Active User {$i}",
                'email' => "active{$i}@example.com",
                'status' => 'active'
            ]);
        }

        $controller = new DataActionTestController();

        $response = $controller->data();
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

    /**
     * Test that data action filters out soft deleted models correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_action_excludes_soft_deleted_models()
    {
        // Create 3 active models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "Active User {$i}",
                'email' => "active{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Create 2 trashed models
        for ($i = 1; $i <= 2; $i++) {
            $model = DataActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com",
                'status' => 'inactive'
            ]);
            $model->delete();
        }

        $controller = new DataActionTestController();

        $response = $controller->data();
        $data = $response->getData(true);

        // Assert only active models are returned
        $this->assertCount(3, $data['data']);
        $this->assertEquals(3, $data['total']);
    }

    // ============== Tests for trashData() action ==============

    /**
     * Test that trash action returns only soft-deleted models
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_returns_only_soft_deleted_models()
    {
        // Create some models
        $activeModel = DataActionTestModel::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'status' => 'active'
        ]);

        $trashedModel1 = DataActionTestModel::create([
            'name' => 'Trashed User 1',
            'email' => 'trashed1@example.com',
            'status' => 'inactive'
        ]);
        $trashedModel1->delete();

        $trashedModel2 = DataActionTestModel::create([
            'name' => 'Trashed User 2',
            'email' => 'trashed2@example.com',
            'status' => 'inactive'
        ]);
        $trashedModel2->delete();

        $controller = new DataActionTestController();

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
        DataActionTestModel::create([
            'name' => 'Active User 1',
            'email' => 'active1@example.com',
            'status' => 'active'
        ]);

        DataActionTestModel::create([
            'name' => 'Active User 2',
            'email' => 'active2@example.com',
            'status' => 'active'
        ]);

        $controller = new DataActionTestController();

        $response = $controller->trashData();
        $data = $response->getData(true);

        // Assert empty result
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that onTrashResponse hook is executed
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_on_trash_response_hook_is_executed()
    {
        $controller = new DataActionTestController();

        $controller->trashData();

        // Assert that the response hook was called
        $this->assertTrue($controller->wasOnTrashResponseCalled());
        $this->assertNotNull($controller->getResponseDataForTrash());
    }

    /**
     * Test that trash action includes pagination metadata
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_trash_action_includes_pagination_metadata()
    {
        // Create multiple trashed models to test pagination
        for ($i = 1; $i <= 5; $i++) {
            $model = DataActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com",
                'status' => 'inactive'
            ]);
            $model->delete();
        }

        $controller = new DataActionTestController();

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

    /**
     * Test that data and trash actions return different results
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_data_and_trash_actions_return_different_results()
    {
        // Create 3 active models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "Active User {$i}",
                'email' => "active{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Create 2 trashed models
        for ($i = 1; $i <= 2; $i++) {
            $model = DataActionTestModel::create([
                'name' => "Trashed User {$i}",
                'email' => "trashed{$i}@example.com",
                'status' => 'inactive'
            ]);
            $model->delete();
        }

        $controller = new DataActionTestController();

        // Get data from both actions
        $dataResponse = $controller->data();
        $dataResults = $dataResponse->getData(true);

        $trashResponse = $controller->trashData();
        $trashResults = $trashResponse->getData(true);

        // Assert different results
        $this->assertCount(3, $dataResults['data']); // Active only
        $this->assertCount(2, $trashResults['data']); // Trashed only

        // Assert no overlap
        $dataNames = array_column($dataResults['data'], 'name');
        $trashNames = array_column($trashResults['data'], 'name');

        $overlap = array_intersect($dataNames, $trashNames);
        $this->assertEmpty($overlap);
    }
}
