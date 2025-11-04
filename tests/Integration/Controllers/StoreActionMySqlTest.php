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
 * Test category model for BelongsTo relation testing (MySQL)
 */
class StoreActionMySqlTestCategory extends Model
{
    protected $table = 'store_action_mysql_test_categories';
    protected $fillable = ['name'];
}

/**
 * Test tag model for BelongsToMany relation testing (MySQL)
 */
class StoreActionMySqlTestTag extends Model
{
    protected $table = 'store_action_mysql_test_tags';
    protected $fillable = ['name'];
}

/**
 * Test comment model for HasMany relation testing (MySQL)
 */
class StoreActionMySqlTestComment extends Model
{
    protected $table = 'store_action_mysql_test_comments';
    protected $fillable = ['content'];

    public function storeActionMySqlTestModel()
    {
        return $this->belongsTo(StoreActionMySqlTestModel::class);
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
     * Test that store action creates record successfully with MySQL
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_creates_record_successfully_mysql()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        $this->controller->resetHookTracker();
        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Assert response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('created', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['created']);
        $this->assertEquals('The message.item.store-action-mysql-test created successfully.', $data['message']);

        // Assert data was created in database
        $this->assertDatabaseHas('store_action_mysql_test_models', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        // Assert hooks were called
        $this->assertTrue($this->controller->wasOnStoreCalled());
        $this->assertTrue($this->controller->wasOnStoreResponseCalled());
    }

    /**
     * Test that store action handles BelongsTo relations correctly with MySQL
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_handles_belongs_to_relation_mysql()
    {
        $category = StoreActionMySqlTestCategory::where('name', 'Technology')->first();

        $request = Request::create('/', 'POST', [
            'name' => 'User with Category',
            'email' => 'category@example.com',
            'age' => 30,
            'category_id' => $category->id
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['created']);

        // Assert data was created with relation
        $this->assertDatabaseHas('store_action_mysql_test_models', [
            'name' => 'User with Category',
            'email' => 'category@example.com',
            'age' => 30,
            'category_id' => $category->id
        ]);

        // Assert relation is loaded correctly
        $model = StoreActionMySqlTestModel::find($data['id']);
        $this->assertEquals($category->id, $model->category_id);
        $this->assertEquals('Technology', $model->category->name);
    }

    /**
     * Test that store action handles BelongsToMany relations correctly with MySQL
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_handles_belongs_to_many_relation_mysql()
    {
        $laravelTag = StoreActionMySqlTestTag::where('name', 'Laravel')->first();
        $phpTag = StoreActionMySqlTestTag::where('name', 'PHP')->first();

        $request = Request::create('/', 'POST', [
            'name' => 'User with Tags',
            'email' => 'tags@example.com',
            'age' => 28,
            'tags' => [$laravelTag->id, $phpTag->id]
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['created']);

        // Assert data was created
        $this->assertDatabaseHas('store_action_mysql_test_models', [
            'name' => 'User with Tags',
            'email' => 'tags@example.com',
            'age' => 28
        ]);

        // Assert pivot table has correct relations
        $this->assertDatabaseHas('store_action_mysql_test_model_tag', [
            'store_action_my_sql_test_model_id' => $data['id'],
            'store_action_my_sql_test_tag_id' => $laravelTag->id
        ]);
        $this->assertDatabaseHas('store_action_mysql_test_model_tag', [
            'store_action_my_sql_test_model_id' => $data['id'],
            'store_action_my_sql_test_tag_id' => $phpTag->id
        ]);

        // Assert relation is loaded correctly
        $model = StoreActionMySqlTestModel::with('tags')->find($data['id']);
        $this->assertCount(2, $model->tags);
        $tagNames = $model->tags->pluck('name')->sort()->values();
        $this->assertEquals(['Laravel', 'PHP'], $tagNames->toArray());
    }

    /**
     * Test that store action handles HasMany relations correctly with MySQL
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_handles_has_many_relation_mysql()
    {
        // First create some comments
        $comment1 = StoreActionMySqlTestComment::create(['content' => 'First comment']);
        $comment2 = StoreActionMySqlTestComment::create(['content' => 'Second comment']);

        $request = Request::create('/', 'POST', [
            'name' => 'User with Comments',
            'email' => 'comments@example.com',
            'age' => 32,
            'comments' => [$comment1->id, $comment2->id]
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['created']);

        // Assert data was created
        $this->assertDatabaseHas('store_action_mysql_test_models', [
            'name' => 'User with Comments',
            'email' => 'comments@example.com',
            'age' => 32
        ]);

        // Assert comments are associated with the model
        $this->assertDatabaseHas('store_action_mysql_test_comments', [
            'id' => $comment1->id,
            'store_action_my_sql_test_model_id' => $data['id']
        ]);
        $this->assertDatabaseHas('store_action_mysql_test_comments', [
            'id' => $comment2->id,
            'store_action_my_sql_test_model_id' => $data['id']
        ]);

        // Assert relation is loaded correctly
        $model = StoreActionMySqlTestModel::with('comments')->find($data['id']);
        $this->assertCount(2, $model->comments);
        $commentContents = $model->comments->pluck('content')->sort()->values();
        $this->assertEquals(['First comment', 'Second comment'], $commentContents->toArray());
    }
}
