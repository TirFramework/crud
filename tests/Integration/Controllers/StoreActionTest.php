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
 * Test model for Store action integration testing
 */
class StoreActionTestModel extends Model
{
    protected $fillable = ['name', 'email', 'age'];

    public function category()
    {
        return $this->belongsTo(StoreActionTestCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(StoreActionTestTag::class, 'store_action_test_model_tag');
    }

    public function comments()
    {
        return $this->hasMany(StoreActionTestComment::class);
    }
}

/**
 * Test category model for BelongsTo relation testing
 */
class StoreActionTestCategory extends Model
{
    protected $fillable = ['name'];
}

/**
 * Test tag model for BelongsToMany relation testing
 */
class StoreActionTestTag extends Model
{
    protected $fillable = ['name'];
}

/**
 * Test comment model for HasMany relation testing
 */
class StoreActionTestComment extends Model
{
    protected $fillable = ['content'];

    public function storeActionTestModel()
    {
        return $this->belongsTo(StoreActionTestModel::class);
    }
}

/**
 * Test scaffolder for Store action integration testing
 */
class StoreActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'store-action-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            Select::make('category_id')
                ->relation('category', 'name')
                ->data(StoreActionTestCategory::all()->map(function($category) {
                    return ['label' => $category->name, 'value' => $category->id];
                })->toArray())
                ->rules(['nullable', 'exists:store_action_test_categories,id']),
            Select::make('tags')
                ->relation('tags', 'name')
                ->data(StoreActionTestTag::all()->map(function($tag) {
                    return ['label' => $tag->name, 'value' => $tag->id];
                })->toArray())
                ->rules(['nullable', 'array']),
            Select::make('comments')
                ->relation('comments', 'content')
                ->data(StoreActionTestComment::all()->map(function($comment) {
                    return ['label' => $comment->content, 'value' => $comment->id];
                })->toArray())
                ->rules(['nullable', 'array']),
        ];
    }

    protected function setModel(): string
    {
        return StoreActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for Store action integration testing
 */
class StoreActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Store;

    protected function setScaffolder(): string
    {
        return StoreActionTestScaffolder::class;
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

    public function getStoreData(): mixed
    {
        return $this->storeData;
    }

    public function getStoreResponseData(): mixed
    {
        return $this->storeResponseData;
    }

    public function resetHookTracker()
    {
        $this->onStoreCalled = false;
        $this->onStoreResponseCalled = false;
        $this->storeData = null;
        $this->storeResponseData = null;
    }
}

class StoreActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private StoreActionTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test tables
        \Illuminate\Support\Facades\Schema::create('store_action_test_categories', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_test_tags', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_test_comments', function ($table) {
            $table->id();
            $table->string('content');
            $table->foreignId('store_action_test_model_id')->nullable()->constrained('store_action_test_models')->onDelete('cascade');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->foreignId('category_id')->nullable()->constrained('store_action_test_categories')->onDelete('set null');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('store_action_test_model_tag', function ($table) {
            $table->id();
            $table->foreignId('store_action_test_model_id')->constrained('store_action_test_models')->onDelete('cascade');
            $table->foreignId('store_action_test_tag_id')->constrained('store_action_test_tags')->onDelete('cascade');
            $table->timestamps();
        });

        // Seed some test data
        StoreActionTestCategory::create(['name' => 'Technology']);
        StoreActionTestCategory::create(['name' => 'Business']);
        StoreActionTestTag::create(['name' => 'Laravel']);
        StoreActionTestTag::create(['name' => 'PHP']);
        StoreActionTestTag::create(['name' => 'Testing']);

        $this->controller = new StoreActionTestController();
    }

    public function test_store_action_creates_record_successfully()
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
        $this->assertEquals('The message.item.store-action-test created successfully.', $data['message']);

        // Assert data was created in database
        $this->assertDatabaseHas('store_action_test_models', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        // Assert hooks were called
        $this->assertTrue($this->controller->wasOnStoreCalled());
        $this->assertTrue($this->controller->wasOnStoreResponseCalled());

        // Assert hook data
        $storeData = $this->controller->getStoreData();
        $this->assertInstanceOf(StoreActionTestModel::class, $storeData);
        $this->assertEquals('John Doe', $storeData->name);
        $this->assertEquals('john@example.com', $storeData->email);
        $this->assertEquals(25, $storeData->age);
    }

    public function test_store_action_validates_request_data()
    {
        // Test missing required field
        $request = Request::create('/', 'POST', [
            'name' => '', // Empty name should fail validation
            'email' => 'john@example.com',
            'age' => 25
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->store($request);
    }

    public function test_store_action_filters_request_fields()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'extra_field' => 'should be filtered out', // Not in scaffold
            'another_extra' => 'also filtered'
        ]);

        $response = $this->controller->store($request);

        // Assert only scaffold fields were saved
        $this->assertDatabaseHas('store_action_test_models', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        // Assert extra fields were not saved (they shouldn't exist in the table anyway)
        $createdRecord = StoreActionTestModel::where('email', 'john@example.com')->first();
        $this->assertNotNull($createdRecord);
        $this->assertEquals('John Doe', $createdRecord->name);
        $this->assertEquals('john@example.com', $createdRecord->email);
        $this->assertEquals(25, $createdRecord->age);
    }

    public function test_store_action_handles_invalid_email()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'invalid-email', // Invalid email format
            'age' => 25
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->store($request);
    }

    public function test_store_action_handles_invalid_age()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => -5 // Invalid age (negative)
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->store($request);
    }

    public function test_store_action_returns_correct_response_structure()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'age' => 30
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Assert response structure
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('created', $data);
        $this->assertArrayHasKey('message', $data);

        // Assert data content
        $this->assertTrue($data['created']);
        $this->assertEquals('The message.item.store-action-test created successfully.', $data['message']);
    }

    public function test_on_store_hook_can_modify_response()
    {
        $controller = new class extends StoreActionTestController {
            protected function setup(): void
            {
                // Override the onStoreResponse hook to modify response
                $this->onStoreResponse(function ($defaultResponse, $item) {
                    $response = $defaultResponse($item);
                    $data = $response->getData(true);
                    $data['message'] = 'Custom success message from hook';
                    $data['custom_field'] = 'added_by_hook';
                    return response()->json($data);
                });
            }
        };

        $request = Request::create('/', 'POST', [
            'name' => 'Hook Modified Response User',
            'email' => 'hook@example.com',
            'age' => 35
        ]);

        $response = $controller->store($request);
        $data = $response->getData(true);

        // Assert the hook modified the response
        $this->assertEquals('Custom success message from hook', $data['message']);
        $this->assertArrayHasKey('custom_field', $data);
        $this->assertEquals('added_by_hook', $data['custom_field']);

        // Assert the data was still saved correctly
        $this->assertDatabaseHas('store_action_test_models', [
            'name' => 'Hook Modified Response User',
            'email' => 'hook@example.com',
            'age' => 35
        ]);
    }

    /**
     * Test that store action handles BelongsTo relations correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_handles_belongs_to_relation()
    {
        $category = StoreActionTestCategory::where('name', 'Technology')->first();

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
        $this->assertDatabaseHas('store_action_test_models', [
            'name' => 'User with Category',
            'email' => 'category@example.com',
            'age' => 30,
            'category_id' => $category->id
        ]);

        // Assert relation is loaded correctly
        $model = StoreActionTestModel::find($data['id']);
        $this->assertEquals($category->id, $model->category_id);
        $this->assertEquals('Technology', $model->category->name);
    }

    /**
     * Test that store action handles BelongsToMany relations correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_handles_belongs_to_many_relation()
    {
        $laravelTag = StoreActionTestTag::where('name', 'Laravel')->first();
        $phpTag = StoreActionTestTag::where('name', 'PHP')->first();

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
        $this->assertDatabaseHas('store_action_test_models', [
            'name' => 'User with Tags',
            'email' => 'tags@example.com',
            'age' => 28
        ]);

        // Assert pivot table has correct relations
        $this->assertDatabaseHas('store_action_test_model_tag', [
            'store_action_test_model_id' => $data['id'],
            'store_action_test_tag_id' => $laravelTag->id
        ]);
        $this->assertDatabaseHas('store_action_test_model_tag', [
            'store_action_test_model_id' => $data['id'],
            'store_action_test_tag_id' => $phpTag->id
        ]);

        // Assert relation is loaded correctly
        $model = StoreActionTestModel::with('tags')->find($data['id']);
        $this->assertCount(2, $model->tags);
        $tagNames = $model->tags->pluck('name')->sort()->values();
        $this->assertEquals(['Laravel', 'PHP'], $tagNames->toArray());
    }

    /**
     * Test that store action handles HasMany relations correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_store_action_handles_has_many_relation()
    {
        // First create some comments
        $comment1 = StoreActionTestComment::create(['content' => 'First comment']);
        $comment2 = StoreActionTestComment::create(['content' => 'Second comment']);

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
        $this->assertDatabaseHas('store_action_test_models', [
            'name' => 'User with Comments',
            'email' => 'comments@example.com',
            'age' => 32
        ]);

        // Assert comments are associated with the model
        $this->assertDatabaseHas('store_action_test_comments', [
            'id' => $comment1->id,
            'store_action_test_model_id' => $data['id']
        ]);
        $this->assertDatabaseHas('store_action_test_comments', [
            'id' => $comment2->id,
            'store_action_test_model_id' => $data['id']
        ]);

        // Assert relation is loaded correctly
        $model = StoreActionTestModel::with('comments')->find($data['id']);
        $this->assertCount(2, $model->comments);
        $commentContents = $model->comments->pluck('content')->sort()->values();
        $this->assertEquals(['First comment', 'Second comment'], $commentContents->toArray());
    }
}
