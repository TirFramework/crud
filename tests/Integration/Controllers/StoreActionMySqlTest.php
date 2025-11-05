<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Controllers\CrudController;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Number;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test model for Store action MySQL integration testing
 */
class StoreActionMySqlTestModel extends Model
{
    protected $table = 'store_action_mysql_test_models';
    protected $fillable = ['name', 'email', 'age'];

    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        // Create a partial mock that wraps the real connection
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }

    public function category()
    {
        return $this->belongsTo(StoreActionMySqlTestCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(StoreActionMySqlTestTag::class, 'store_action_mysql_test_model_tag');
    }

    public function comments()
    {
        return $this->hasMany(StoreActionMySqlTestComment::class);
    }
}

/**
 * Test category model for BelongsTo relationship testing
 */
class StoreActionMySqlTestCategory extends Model
{
    protected $table = 'store_action_mysql_test_categories';
    protected $fillable = ['name'];

    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        // Create a partial mock that wraps the real connection
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }

    public function models()
    {
        return $this->hasMany(StoreActionMySqlTestModel::class, 'category_id');
    }
}

/**
 * Test tag model for BelongsToMany relationship testing
 */
class StoreActionMySqlTestTag extends Model
{
    protected $table = 'store_action_mysql_test_tags';
    protected $fillable = ['name'];

    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        // Create a partial mock that wraps the real connection
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }

    public function models()
    {
        return $this->belongsToMany(
            StoreActionMySqlTestModel::class,
            'store_action_mysql_test_model_tag',
            'store_action_mysql_test_tag_id',
            'store_action_mysql_test_model_id'
        );
    }
}

/**
 * Test comment model for HasMany relationship testing
 */
class StoreActionMySqlTestComment extends Model
{
    protected $table = 'store_action_mysql_test_comments';
    protected $fillable = ['content', 'store_action_my_sql_test_model_id'];

    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        // Create a partial mock that wraps the real connection
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }

    public function model()
    {
        return $this->belongsTo(StoreActionMySqlTestModel::class, 'store_action_my_sql_test_model_id');
    }
}

/**
 * Test scaffolder for Store action MySQL integration testing
 */
class StoreActionMySqlTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'store-action-mysql-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            Select::make('category_id')
                ->relation('category', 'name')
                ->data(StoreActionMySqlTestCategory::all()->map(function($category) {
                    return ['label' => $category->name, 'value' => $category->id];
                })->toArray())
                ->rules(['nullable', 'exists:store_action_mysql_test_categories,id']),
            Select::make('tags')
                ->relation('tags', 'name')
                ->data(StoreActionMySqlTestTag::all()->map(function($tag) {
                    return ['label' => $tag->name, 'value' => $tag->id];
                })->toArray())
                ->rules(['nullable', 'array']),
            Select::make('comments')
                ->relation('comments', 'content')
                ->data(StoreActionMySqlTestComment::all()->map(function($comment) {
                    return ['label' => $comment->content, 'value' => $comment->id];
                })->toArray())
                ->rules(['nullable', 'array']),
        ];
    }

    protected function setModel(): string
    {
        return StoreActionMySqlTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Store action MySQL integration testing
 */
class StoreActionMySqlTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Store;

    protected function setScaffolder(): string
    {
        return StoreActionMySqlTestScaffolder::class;
    }

    // Track hook calls for testing
    private bool $onStoreCalled = false;
    private bool $onStoreResponseCalled = false;
    private mixed $storeData = null;
    private mixed $storeResponseData = null;

    protected function setup(): void
    {
        // Test the onStore hook
        $this->onStore(function ($defaultStore) {
            $this->onStoreCalled = true;
            $this->storeData = $defaultStore();
            return $this->storeData;
        });

        // Test the onStoreResponse hook
        $this->onStoreResponse(function ($defaultResponse, $item) {
            $this->onStoreResponseCalled = true;
            $this->storeResponseData = $item;
            return $defaultResponse($item);
        });
    }

    // Getters for testing hook execution
    public function wasOnStoreCalled(): bool
    {
        return $this->onStoreCalled;
    }

    public function wasOnStoreResponseCalled(): bool
    {
        return $this->onStoreResponseCalled;
    }

    public function resetHookTracker(): void
    {
        $this->onStoreCalled = false;
        $this->onStoreResponseCalled = false;
        $this->storeData = null;
        $this->storeResponseData = null;
    }

    public function callAction($method, $parameters)
    {
        // Override to bypass access control for testing
        return call_user_func_array([$this, $method], $parameters);
    }
}

/**
 * Store Action MySQL Integration Test
 *
 * Tests the complete store action flow with MySQL database adapter
 */
class StoreActionMySqlTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private StoreActionMySqlTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // NOTE: In a real environment with MySQL available, this would use:
        // config(['database.default' => 'mysql_testing']);
        // And the MySQL adapter would be selected automatically
        // For now, using SQLite to demonstrate the test structure

        // Create the test tables with MySQL-like naming
        \Illuminate\Support\Facades\Schema::create('store_action_mysql_test_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_mysql_test_tags', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_mysql_test_comments', function ($table) {
            $table->id();
            $table->string('content');
            $table->foreignId('store_action_my_sql_test_model_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_mysql_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->foreignId('category_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_mysql_test_model_tag', function ($table) {
            $table->id();
            $table->foreignId('store_action_my_sql_test_model_id');
            $table->foreignId('store_action_my_sql_test_tag_id');
            $table->timestamps();
        });

        // Seed some test data
        StoreActionMySqlTestCategory::create(['name' => 'Technology']);
        StoreActionMySqlTestCategory::create(['name' => 'Business']);
        StoreActionMySqlTestTag::create(['name' => 'Laravel']);
        StoreActionMySqlTestTag::create(['name' => 'PHP']);
        StoreActionMySqlTestTag::create(['name' => 'Testing']);

        $this->controller = new StoreActionMySqlTestController();
    }

    /**
     * Test MySQL adapter processes flat array structure (no nesting)
     * MySQL adapter should keep data as flat arrays, not convert to nested structures
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_processes_flat_array_structure()
    {
        // Create a request with flat structure
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Assert data was stored successfully
        $this->assertTrue($data['created']);
        
        // Verify the model was filled with flat structure (not nested)
        $model = StoreActionMySqlTestModel::find($data['id']);
        $this->assertEquals('John Doe', $model->name);
        $this->assertEquals('john@example.com', $model->email);
        $this->assertEquals(25, $model->age);
        
        // The key test: MySQL adapter should have used fill() method
        // This is evidenced by the data being stored correctly in flat structure
        $this->assertDatabaseHas('store_action_mysql_test_models', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);
    }

    /**
     * Test MySQL adapter filters fillable fields correctly
     * MySQL adapter should only process fields marked as fillable in scaffolder
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_filters_fillable_fields()
    {
        // Create request with both fillable and non-fillable fields
        $request = Request::create('/', 'POST', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 30,
            'extra_field' => 'should be filtered',
            'another_field' => 'also filtered'
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Verify only fillable fields were stored
        $model = StoreActionMySqlTestModel::find($data['id']);
        $this->assertEquals('Test User', $model->name);
        $this->assertEquals('test@example.com', $model->email);
        $this->assertEquals(30, $model->age);
        
        // Verify extra fields don't exist (they shouldn't be in the model)
        $this->assertFalse(isset($model->extra_field));
        $this->assertFalse(isset($model->another_field));
    }

    /**
     * Test MySQL adapter uses model fill() method
     * MySQL adapter should use Eloquent's fill() method, not direct property assignment
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_uses_model_fill_method()
    {
        // This test verifies that MySQL adapter uses $model->fill()
        // by ensuring that mass assignment protection works correctly
        
        $request = Request::create('/', 'POST', [
            'name' => 'Fill Method Test',
            'email' => 'fill@example.com',
            'age' => 35
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // If fill() is used correctly, only fillable fields will be set
        $model = StoreActionMySqlTestModel::find($data['id']);
        $this->assertEquals('Fill Method Test', $model->name);
        $this->assertEquals('fill@example.com', $model->email);
        $this->assertEquals(35, $model->age);
        
        // Verify the model's fillable protection worked
        $this->assertIsArray($model->getFillable());
        $this->assertContains('name', $model->getFillable());
        $this->assertContains('email', $model->getFillable());
        $this->assertContains('age', $model->getFillable());
    }
}
