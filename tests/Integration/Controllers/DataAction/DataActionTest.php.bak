<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Controllers\CrudController;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\DatePicker;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Tir\Crud\Support\Enums\FilterType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test related model for relational filtering
 */
class DataActionTestCategory extends Model
{
    protected $table = 'data_action_test_categories';
    protected $fillable = ['name'];
}

/**
 * Test related model for many-to-many filtering
 */
class DataActionTestTag extends Model
{
    protected $table = 'data_action_test_tags';
    protected $fillable = ['name'];
}

/**
 * Test model for Data action integration testing
 */
class DataActionTestModel extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'status', 'priority', 'score', 'created_date', 'description', 'category_id', 'custom_filter_field'];
    protected $casts = [
        'created_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(DataActionTestCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(DataActionTestTag::class, 'data_action_test_model_tag', 'model_id', 'tag_id');
    }
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
            Text::make('name')->rules('required|string|max:255')->searchable(),
            Text::make('email')->rules('required|email')->searchable(),
            Text::make('status')->rules('required|string')
                ->searchable()
                ->searchQuery(function($query, $searchTerm) {
                    // Custom search: convert search term to uppercase and search status
                    return $query->orWhere('status', 'like', strtoupper($searchTerm) . '%');
                })
                ->filter()
                ->filterType(FilterType::Select),
            Text::make('priority')->rules('required|string')
                ->filter()
                ->filterType(FilterType::Select),
            Text::make('score')->rules('required|integer')
                ->filter()
                ->filterType(FilterType::Slider),
            DatePicker::make('created_date')->rules('nullable|date')
                ->filter()
                ->filterType(FilterType::DatePicker),
            Text::make('description')->rules('nullable|string')
                ->filter()
                ->filterType(FilterType::Search),
            Select::make('category_id')
                ->relation('category', 'name', 'id')
                ->filter()
                ->filterType(FilterType::Select),
            Text::make('custom_filter_field')->rules('nullable|string')
                ->filterQuery(function($query, $value) {
                    // Custom filter: find records where score > 50 AND priority matches value
                    return $query->where('score', '>', 50)->whereIn('priority', (array)$value);
                }),
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
}

class DataActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the categories test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create the tags test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_tags', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('data_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status');
            $table->string('priority')->default('medium');
            $table->integer('score')->default(0);
            $table->date('created_date')->nullable();
            $table->text('description')->nullable();
            $table->string('custom_filter_field')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('data_action_test_categories')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });

        // Create the pivot table for many-to-many relationship
        \Illuminate\Support\Facades\Schema::create('data_action_test_model_tag', function ($table) {
            $table->id();
            $table->foreignId('model_id')->constrained('data_action_test_models')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('data_action_test_tags')->onDelete('cascade');
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

    // ============== Tests for search functionality ==============

    /**
     * Test that search works with no search parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_returns_all_models_when_no_search_parameter()
    {
        // Create test models
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active'
            ]);
        }

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Without search parameter, all models should be returned
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that search filters by name field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_filters_by_name_field()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Johnny Walker',
            'email' => 'johnny@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'John']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 'John Smith' and 'Johnny Walker'
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('John Smith', $names);
        $this->assertContains('Johnny Walker', $names);
    }

    /**
     * Test that search filters by email field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_filters_by_email_field()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'User One',
            'email' => 'john@company.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User Two',
            'email' => 'jane@company.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'User Three',
            'email' => 'john@other.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'company']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find users with 'company' in email
        $this->assertCount(2, $data['data']);
        $emails = array_column($data['data'], 'email');
        $this->assertContains('john@company.com', $emails);
        $this->assertContains('jane@company.com', $emails);
    }

    /**
     * Test that search with multiple fields (OR logic)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_applies_or_logic_across_searchable_fields()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Charlie Johnson',
            'email' => 'charlie@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'Johnson']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 'Alice Johnson' and 'Charlie Johnson'
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('Alice Johnson', $names);
        $this->assertContains('Charlie Johnson', $names);
    }

    /**
     * Test that search returns empty when no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_returns_empty_when_no_matches()
    {
        // Create test models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active'
            ]);
        }

        // Mock request with search parameter
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'NonExistentUser']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return empty results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test that search is case-insensitive
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_is_case_insensitive()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter in uppercase
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'JOHN']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find the user despite case difference
        $this->assertCount(1, $data['data']);
        $this->assertEquals('John Doe', $data['data'][0]['name']);
    }

    /**
     * Test search with special characters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_special_characters()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => "O'Brien",
            'email' => "obrien@example.com",
            'status' => 'active'
        ]);
        DataActionTestModel::create([
            'name' => 'Smith',
            'email' => 'smith@example.com',
            'status' => 'active'
        ]);

        // Mock request with search parameter containing apostrophe
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => "O'Brien"]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find the user with special character
        $this->assertCount(1, $data['data']);
        $this->assertEquals("O'Brien", $data['data'][0]['name']);
    }

    /**
     * Test search with custom searchQuery callback on status field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_custom_search_query_callback()
    {
        // Create test models with different status values
        DataActionTestModel::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'status' => 'ACTIVE'
        ]);
        DataActionTestModel::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'status' => 'INACTIVE'
        ]);
        DataActionTestModel::create([
            'name' => 'User Three',
            'email' => 'user3@example.com',
            'status' => 'PENDING'
        ]);

        // Mock request with search parameter
        // The status field has a custom searchQuery that converts search term to uppercase
        // and searches with 'like' pattern matching starting with the term
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'ACT']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only the user with status starting with 'ACT' (ACTIVE)
        $this->assertCount(1, $data['data']);
        $this->assertEquals('ACTIVE', $data['data'][0]['status']);
    }

    /**
     * Test search with custom searchQuery callback matches multiple results
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_search_with_custom_search_query_callback_multiple_matches()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'status' => 'PENDING'
        ]);
        DataActionTestModel::create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'status' => 'PENDING'
        ]);
        DataActionTestModel::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'status' => 'ACTIVE'
        ]);

        // Mock request with search parameter for 'PEN' which should match 'PENDING'
        $request = \Illuminate\Http\Request::create('/', 'GET', ['search' => 'PEN']);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find both users with status starting with 'PEN' (PENDING)
        $this->assertCount(2, $data['data']);
        foreach ($data['data'] as $item) {
            $this->assertEquals('PENDING', $item['status']);
        }
    }

    // ============== Tests for filter functionality ==============

    /**
     * Test that filters returns all models when no filter parameter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filters_returns_all_models_when_no_filters_parameter()
    {
        // Create test models
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'high',
                'score' => 10 + $i
            ]);
        }

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Without filter parameter, all models should be returned
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that Select filter works correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_select_type()
    {
        // Create test models with different statuses
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'inactive',
            'priority' => 'low',
            'score' => 30
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 40
        ]);

        // Mock request with Select filter (status = 'active')
        $filters = json_encode(['status' => ['active']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only active users
        $this->assertCount(2, $data['data']);
        foreach ($data['data'] as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    /**
     * Test that Slider filter works with range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_slider_type()
    {
        // Create test models with different scores
        DataActionTestModel::create([
            'name' => 'Low Score',
            'email' => 'low@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 20
        ]);
        DataActionTestModel::create([
            'name' => 'Medium Score',
            'email' => 'medium@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50
        ]);
        DataActionTestModel::create([
            'name' => 'High Score',
            'email' => 'high@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 80
        ]);

        // Mock request with Slider filter (score between 40 and 70)
        $filters = json_encode(['score' => [40, 70]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only medium score user
        $this->assertCount(1, $data['data']);
        $this->assertEquals(50, $data['data'][0]['score']);
    }

    /**
     * Test that Slider filter works with full range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_slider_type_full_range()
    {
        // Create test models with different scores
        for ($i = 1; $i <= 5; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 10 * $i  // 10, 20, 30, 40, 50
            ]);
        }

        // Mock request with Slider filter (score between 0 and 100)
        $filters = json_encode(['score' => [0, 100]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find all users
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test that Slider filter returns empty when out of range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_slider_type_no_matches()
    {
        // Create test models
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 10 + $i  // 11, 12, 13
            ]);
        }

        // Mock request with Slider filter (score between 50 and 100)
        $filters = json_encode(['score' => [50, 100]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test Select filter with multiple values (whereIn)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_select_multiple_values()
    {
        // Create test models with different priorities
        DataActionTestModel::create([
            'name' => 'High Priority',
            'email' => 'high@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 80
        ]);
        DataActionTestModel::create([
            'name' => 'Low Priority',
            'email' => 'low@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 20
        ]);
        DataActionTestModel::create([
            'name' => 'Medium Priority',
            'email' => 'medium@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50
        ]);

        // Mock request with Select filter (priority in ['high', 'low'])
        $filters = json_encode(['priority' => ['high', 'low']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find high and low priority users
        $this->assertCount(2, $data['data']);
        $priorities = array_column($data['data'], 'priority');
        $this->assertContains('high', $priorities);
        $this->assertContains('low', $priorities);
        $this->assertNotContains('medium', $priorities);
    }

    // ============== Tests for DatePicker filter functionality ==============

    /**
     * Test DatePicker filter filters by date range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_type()
    {
        // Create test models with different dates
        DataActionTestModel::create([
            'name' => 'Early User',
            'email' => 'early@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-01-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Mid User',
            'email' => 'mid@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Late User',
            'email' => 'late@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-12-15'
        ]);

        // Mock request with DatePicker filter (between 2024-05-01 and 2024-07-31)
        $filters = json_encode(['created_date' => ['2024-05-01', '2024-07-31']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only mid user
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Mid User', $data['data'][0]['name']);
    }

    /**
     * Test DatePicker filter returns all records in wide date range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_wide_range()
    {
        // Create test models with dates in 2024
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 50,
                'created_date' => "2024-0{$i}-15"
            ]);
        }

        // Mock request with DatePicker filter (whole year 2024)
        $filters = json_encode(['created_date' => ['2024-01-01', '2024-12-31']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find all users
        $this->assertCount(3, $data['data']);
    }

    /**
     * Test DatePicker filter returns empty when out of date range
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_no_matches()
    {
        // Create test models with dates in 2024
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-01-05'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-01-10'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-01-15'
        ]);

        // Mock request with DatePicker filter (year 2023)
        $filters = json_encode(['created_date' => ['2023-01-01', '2023-12-31']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test DatePicker filter with single date (exact match)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_date_picker_exact_date()
    {
        // Create test models with different dates
        DataActionTestModel::create([
            'name' => 'Target User',
            'email' => 'target@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15'
        ]);
        DataActionTestModel::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-16'
        ]);

        // Mock request with DatePicker filter (exact date)
        $filters = json_encode(['created_date' => ['2024-06-15', '2024-06-15']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only target user
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Target User', $data['data'][0]['name']);
    }

    // ============== Tests for Search filter type ==============

    /**
     * Test Search filter type (like search filter)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type()
    {
        // Create test models with different descriptions
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'High priority task'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Medium priority task'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Low priority meeting'
        ]);

        // Mock request with Search filter type (contains 'task')
        $filters = json_encode(['description' => 'task']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only tasks (User 1 and User 2)
        $this->assertCount(2, $data['data']);
        $descriptions = array_column($data['data'], 'description');
        $this->assertContains('High priority task', $descriptions);
        $this->assertContains('Medium priority task', $descriptions);
    }

    /**
     * Test Search filter type with no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type_no_matches()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'High priority task'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Medium priority task'
        ]);

        // Mock request with Search filter type (search for non-existent term)
        $filters = json_encode(['description' => 'nonexistent']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test Search filter type with partial match
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type_partial_match()
    {
        // Create test models
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Implementation of new features'
        ]);
        DataActionTestModel::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Bug implementation report'
        ]);
        DataActionTestModel::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Documentation review'
        ]);

        // Mock request with Search filter type (partial match 'impl')
        $filters = json_encode(['description' => 'impl']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find users with 'impl' in description
        $this->assertCount(2, $data['data']);
        $descriptions = array_column($data['data'], 'description');
        $this->assertContains('Implementation of new features', $descriptions);
        $this->assertContains('Bug implementation report', $descriptions);
    }

    /**
     * Test Search filter type is case-insensitive
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_search_type_case_insensitive()
    {
        // Create test model
        DataActionTestModel::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Urgent Production Issue'
        ]);

        // Mock request with Search filter type (lowercase search for uppercase text)
        $filters = json_encode(['description' => 'urgent']);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find the user despite case difference
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Urgent Production Issue', $data['data'][0]['description']);
    }

    // ============== Tests for relational filters ==============

    /**
     * Test relational filter with whereHas for single relation match
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_single_match()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);
        $cat2 = DataActionTestCategory::create(['name' => 'Design']);

        // Create users with categories
        DataActionTestModel::create([
            'name' => 'Developer 1',
            'email' => 'dev1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Backend developer',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Developer 2',
            'email' => 'dev2@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 60,
            'created_date' => '2024-06-15',
            'description' => 'Frontend developer',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Designer 1',
            'email' => 'designer@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 40,
            'created_date' => '2024-06-15',
            'description' => 'UI designer',
            'category_id' => $cat2->id
        ]);

        // Mock request with relational filter (category_id = Development)
        $filters = json_encode(['category_id' => [$cat1->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only developers
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('Developer 1', $names);
        $this->assertContains('Developer 2', $names);
    }

    /**
     * Test relational filter with whereHas for multiple relations
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_multiple_matches()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);
        $cat2 = DataActionTestCategory::create(['name' => 'Design']);
        $cat3 = DataActionTestCategory::create(['name' => 'Marketing']);

        // Create users with categories
        for ($i = 1; $i <= 2; $i++) {
            DataActionTestModel::create([
                'name' => "Dev {$i}",
                'email' => "dev{$i}@example.com",
                'status' => 'active',
                'priority' => 'high',
                'score' => 50,
                'created_date' => '2024-06-15',
                'description' => 'Developer',
                'category_id' => $cat1->id
            ]);
        }
        for ($i = 1; $i <= 2; $i++) {
            DataActionTestModel::create([
                'name' => "Designer {$i}",
                'email' => "designer{$i}@example.com",
                'status' => 'active',
                'priority' => 'medium',
                'score' => 40,
                'created_date' => '2024-06-15',
                'description' => 'Designer',
                'category_id' => $cat2->id
            ]);
        }
        DataActionTestModel::create([
            'name' => 'Marketer 1',
            'email' => 'marketer@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Marketing specialist',
            'category_id' => $cat3->id
        ]);

        // Mock request with relational filter (category_id in [Development, Design])
        $filters = json_encode(['category_id' => [$cat1->id, $cat2->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find developers and designers (4 total)
        $this->assertCount(4, $data['data']);
    }

    /**
     * Test relational filter returns empty when no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_no_matches()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);
        $cat2 = DataActionTestCategory::create(['name' => 'Design']);
        $cat_unused = DataActionTestCategory::create(['name' => 'Unused']);

        // Create users only with cat1 and cat2
        DataActionTestModel::create([
            'name' => 'Developer 1',
            'email' => 'dev@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Developer',
            'category_id' => $cat1->id
        ]);

        // Mock request with relational filter (category_id = unused)
        $filters = json_encode(['category_id' => [$cat_unused->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test relational filter with null category_id
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_relational_excludes_null()
    {
        // Create category
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);

        // Create users with and without category
        DataActionTestModel::create([
            'name' => 'User with category',
            'email' => 'withcat@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Has category',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'User without category',
            'email' => 'nocat@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'No category',
            'category_id' => null
        ]);

        // Mock request with relational filter (category_id = cat1)
        $filters = json_encode(['category_id' => [$cat1->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only user with category
        $this->assertCount(1, $data['data']);
        $this->assertEquals('User with category', $data['data'][0]['name']);
    }

    // ============== Tests for custom filter query ==============

    /**
     * Test custom filter query callback with score condition and priority matching
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_with_score_and_priority()
    {
        // Create users with different scores and priorities
        DataActionTestModel::create([
            'name' => 'High score high priority',
            'email' => 'high1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 75,
            'created_date' => '2024-06-15',
            'description' => 'Score 75'
        ]);
        DataActionTestModel::create([
            'name' => 'High score medium priority',
            'email' => 'high2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 80,
            'created_date' => '2024-06-15',
            'description' => 'Score 80'
        ]);
        DataActionTestModel::create([
            'name' => 'Low score high priority',
            'email' => 'low1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Score 30'
        ]);
        DataActionTestModel::create([
            'name' => 'Borderline score',
            'email' => 'border@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Score 50'
        ]);

        // Mock request with custom filter query (score > 50 AND priority = high)
        $filters = json_encode(['custom_filter_field' => ['high']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only high score + high priority record
        $this->assertCount(1, $data['data']);
        $this->assertEquals('High score high priority', $data['data'][0]['name']);
        $this->assertEquals(75, $data['data'][0]['score']);
    }

    /**
     * Test custom filter query with multiple priority values
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_multiple_priorities()
    {
        // Create users with different scores and priorities
        DataActionTestModel::create([
            'name' => 'High score high priority',
            'email' => 'high1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 75,
            'created_date' => '2024-06-15',
            'description' => 'Score 75'
        ]);
        DataActionTestModel::create([
            'name' => 'High score medium priority',
            'email' => 'high2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 80,
            'created_date' => '2024-06-15',
            'description' => 'Score 80'
        ]);
        DataActionTestModel::create([
            'name' => 'High score low priority',
            'email' => 'high3@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 60,
            'created_date' => '2024-06-15',
            'description' => 'Score 60'
        ]);
        DataActionTestModel::create([
            'name' => 'Low score high priority',
            'email' => 'low1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Score 30'
        ]);

        // Mock request with custom filter query (score > 50 AND priority in [high, medium])
        $filters = json_encode(['custom_filter_field' => ['high', 'medium']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find 2 records: high priority (75) and medium priority (80)
        $this->assertCount(2, $data['data']);
        $names = array_column($data['data'], 'name');
        $this->assertContains('High score high priority', $names);
        $this->assertContains('High score medium priority', $names);
    }

    /**
     * Test custom filter query returns empty when no matches
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_no_matches()
    {
        // Create users with low scores
        DataActionTestModel::create([
            'name' => 'Low score high priority',
            'email' => 'low1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 30,
            'created_date' => '2024-06-15',
            'description' => 'Score 30'
        ]);
        DataActionTestModel::create([
            'name' => 'Low score medium priority',
            'email' => 'low2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 40,
            'created_date' => '2024-06-15',
            'description' => 'Score 40'
        ]);

        // Mock request with custom filter query (score > 50 AND priority = high)
        // No records match this criteria (score > 50)
        $filters = json_encode(['custom_filter_field' => ['high']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should return no results
        $this->assertCount(0, $data['data']);
    }

    /**
     * Test custom filter query with specific boundary condition (score = 51)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_filter_custom_query_boundary_score()
    {
        // Create user at boundary
        DataActionTestModel::create([
            'name' => 'Score 51 low priority',
            'email' => 'boundary@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 51,
            'created_date' => '2024-06-15',
            'description' => 'Just above boundary'
        ]);
        DataActionTestModel::create([
            'name' => 'Score 50 low priority',
            'email' => 'exact@example.com',
            'status' => 'active',
            'priority' => 'low',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'At boundary'
        ]);

        // Mock request with custom filter query (score > 50 AND priority = low)
        $filters = json_encode(['custom_filter_field' => ['low']]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only score 51 (not 50, because condition is > 50, not >= 50)
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Score 51 low priority', $data['data'][0]['name']);
        $this->assertEquals(51, $data['data'][0]['score']);
    }
    // ============== Tests for getFilter() method with relation type detection ==============

    /**
     * Test getFilter() correctly identifies belongsTo relation type
     * This tests lines 399-414 in DataService.php - relation type detection
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_filter_detects_belongs_to_relation()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Category 1']);
        $cat2 = DataActionTestCategory::create(['name' => 'Category 2']);

        // Create users with different categories
        DataActionTestModel::create([
            'name' => 'User in category 1',
            'email' => 'user1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'First user',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'User in category 2',
            'email' => 'user2@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 50,
            'created_date' => '2024-06-15',
            'description' => 'Second user',
            'category_id' => $cat2->id
        ]);

        // Mock request with belongsTo relation filter
        // This will trigger getFilter() which should detect the relation type
        $filters = json_encode(['category_id' => [$cat1->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should correctly filter by the belongsTo relation
        $this->assertCount(1, $data['data']);
        $this->assertEquals('User in category 1', $data['data'][0]['name']);
        $this->assertEquals($cat1->id, $data['data'][0]['category_id']);
    }

    /**
     * Test getFilter() builds correct relational filter structure
     * Tests that the relational array contains correct relation name and primary key
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_filter_builds_relational_filter_structure()
    {
        // Create categories
        $cat1 = DataActionTestCategory::create(['name' => 'Tech']);
        $cat2 = DataActionTestCategory::create(['name' => 'Business']);

        // Create users
        DataActionTestModel::create([
            'name' => 'Tech User 1',
            'email' => 'tech1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 75,
            'created_date' => '2024-06-15',
            'description' => 'Tech person',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Tech User 2',
            'email' => 'tech2@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 80,
            'created_date' => '2024-06-15',
            'description' => 'Tech person 2',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Business User',
            'email' => 'biz@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 60,
            'created_date' => '2024-06-15',
            'description' => 'Business person',
            'category_id' => $cat2->id
        ]);

        // Mock request with multiple category IDs (tests whereIn in relational filter)
        $filters = json_encode(['category_id' => [$cat1->id, $cat2->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find all 3 users (both categories match)
        $this->assertCount(3, $data['data']);
    }

    /**
     * Test getFilter() handles single ID in relational filter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_filter_relational_with_single_id()
    {
        // Create category
        $cat1 = DataActionTestCategory::create(['name' => 'Development']);

        // Create users
        for ($i = 1; $i <= 3; $i++) {
            DataActionTestModel::create([
                'name' => "Dev User {$i}",
                'email' => "dev{$i}@example.com",
                'status' => 'active',
                'priority' => 'high',
                'score' => 50 + $i * 10,
                'created_date' => '2024-06-15',
                'description' => "Developer {$i}",
                'category_id' => $cat1->id
            ]);
        }

        // Mock request with single category (as array with one ID)
        $filters = json_encode(['category_id' => [$cat1->id]]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find all 3 developers
        $this->assertCount(3, $data['data']);
        foreach ($data['data'] as $user) {
            $this->assertEquals($cat1->id, $user['category_id']);
        }
    }

    /**
     * Test getFilter() with relational filter combined with other filters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_filter_relational_combined_with_other_filters()
    {
        // Create category
        $cat1 = DataActionTestCategory::create(['name' => 'Senior']);

        // Create users with different priorities
        DataActionTestModel::create([
            'name' => 'Senior High Priority',
            'email' => 'senior1@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 85,
            'created_date' => '2024-06-15',
            'description' => 'Senior dev',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Senior Medium Priority',
            'email' => 'senior2@example.com',
            'status' => 'active',
            'priority' => 'medium',
            'score' => 75,
            'created_date' => '2024-06-15',
            'description' => 'Senior dev 2',
            'category_id' => $cat1->id
        ]);
        DataActionTestModel::create([
            'name' => 'Other High Priority',
            'email' => 'other@example.com',
            'status' => 'active',
            'priority' => 'high',
            'score' => 80,
            'created_date' => '2024-06-15',
            'description' => 'Other category',
            'category_id' => null
        ]);

        // Mock request with both relational filter AND Select filter
        $filters = json_encode([
            'category_id' => [$cat1->id],
            'priority' => ['high']
        ]);
        $request = \Illuminate\Http\Request::create('/', 'GET', ['filters' => $filters]);
        \Illuminate\Support\Facades\Request::swap($request);

        $controller = new DataActionTestController();
        $response = $controller->data();
        $data = $response->getData(true);

        // Should find only 1 record: high priority in senior category
        $this->assertCount(1, $data['data']);
        $this->assertEquals('Senior High Priority', $data['data'][0]['name']);
        $this->assertEquals('high', $data['data'][0]['priority']);
        $this->assertEquals($cat1->id, $data['data'][0]['category_id']);
    }
}
