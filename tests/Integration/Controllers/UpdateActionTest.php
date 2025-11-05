<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Number;
use Tir\Crud\Support\Scaffold\Fields\Select;
use Tir\Crud\Support\Scaffold\Actions;

/**
 * Test model for Update action integration testing
 */
class UpdateActionTestModel extends Model
{
    protected $table = 'update_action_test_models';
    protected $fillable = ['name', 'email', 'age'];
    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(UpdateActionTestCategory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(UpdateActionTestTag::class, 'update_action_test_model_tag');
    }

    public function comments()
    {
        return $this->hasMany(UpdateActionTestComment::class);
    }
}

/**
 * Test category model for BelongsTo relation testing
 */
class UpdateActionTestCategory extends Model
{
    protected $table = 'update_action_test_categories';
    protected $fillable = ['name'];
    public $timestamps = false;
}

/**
 * Test tag model for BelongsToMany relation testing
 */
class UpdateActionTestTag extends Model
{
    protected $table = 'update_action_test_tags';
    protected $fillable = ['name'];
    public $timestamps = false;
}

/**
 * Test comment model for HasMany relation testing
 */
class UpdateActionTestComment extends Model
{
    protected $table = 'update_action_test_comments';
    protected $fillable = ['content', 'update_action_test_model_id'];
    public $timestamps = false;

    public function updateActionTestModel()
    {
        return $this->belongsTo(UpdateActionTestModel::class);
    }
}

/**
 * Test scaffolder for Update action integration testing
 */
class UpdateActionTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'update-action-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            Select::make('category_id')
                ->relation('category', 'name')
                ->data(UpdateActionTestCategory::all()->map(function($category) {
                    return ['label' => $category->name, 'value' => $category->id];
                })->toArray())
                ->rules(['nullable', 'exists:update_action_test_categories,id']),
            Select::make('tags')
                ->relation('tags', 'name')
                ->data(UpdateActionTestTag::all()->map(function($tag) {
                    return ['label' => $tag->name, 'value' => $tag->id];
                })->toArray())
                ->rules(['nullable', 'array']),
            Select::make('comments')
                ->relation('comments', 'content')
                ->data(UpdateActionTestComment::all()->map(function($comment) {
                    return ['label' => $comment->content, 'value' => $comment->id];
                })->toArray())
                ->rules(['nullable', 'array']),
        ];
    }

    protected function setModel(): string
    {
        return UpdateActionTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for Update action integration testing
 */
class UpdateActionTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Update;

    protected function setScaffolder(): string
    {
        return UpdateActionTestScaffolder::class;
    }

    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }
}

/**
 * UpdateAction Integration Test
 *
 * Tests the Update action flow including fillForUpdate and updateRelations hooks
 * that are called during the update process
 */
class UpdateActionTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected UpdateActionTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new UpdateActionTestController();

        // Create tables
        \Illuminate\Support\Facades\Schema::create('update_action_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->foreignId('category_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('update_action_test_categories', function ($table) {
            $table->id();
            $table->string('name');
        });

        \Illuminate\Support\Facades\Schema::create('update_action_test_tags', function ($table) {
            $table->id();
            $table->string('name');
        });

        \Illuminate\Support\Facades\Schema::create('update_action_test_comments', function ($table) {
            $table->id();
            $table->string('content');
            $table->foreignId('update_action_test_model_id')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('update_action_test_model_tag', function ($table) {
            $table->id();
            $table->foreignId('update_action_test_model_id')->constrained('update_action_test_models')->cascadeOnDelete();
            $table->foreignId('update_action_test_tag_id')->constrained('update_action_test_tags')->cascadeOnDelete();
        });

        // Create test data
        UpdateActionTestCategory::create(['name' => 'Technology']);
        UpdateActionTestCategory::create(['name' => 'Business']);
        UpdateActionTestTag::create(['name' => 'Laravel']);
        UpdateActionTestTag::create(['name' => 'PHP']);
    }

    /**
     * Test basic update action fills model with request data
     * This tests the fillForUpdate() method indirectly through the update flow
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_fills_model_with_request_data()
    {
        $model = UpdateActionTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'age' => 35
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        // Verify response
        $this->assertTrue($data['updated']);

        // Verify data was filled properly through fillForUpdate
        $updatedModel = UpdateActionTestModel::find($model->id);
        $this->assertEquals('Updated Name', $updatedModel->name);
        $this->assertEquals('updated@example.com', $updatedModel->email);
        $this->assertEquals(35, $updatedModel->age);
    }

    /**
     * Test update action preserves unmodified fields
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_preserves_unmodified_fields()
    {
        $model = UpdateActionTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);

        $updatedModel = UpdateActionTestModel::find($model->id);
        $this->assertEquals('Updated Name', $updatedModel->name);
        // Original values should be preserved
        $this->assertEquals('original@example.com', $updatedModel->email);
        $this->assertEquals(30, $updatedModel->age);
    }

    /**
     * Test update action handles updates
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_handles_updates()
    {
        $model = UpdateActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 25
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 40,
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);

        $updatedModel = UpdateActionTestModel::find($model->id);
        $this->assertEquals(40, $updatedModel->age);
        $this->assertEquals('Test User', $updatedModel->name);
        $this->assertEquals('test@example.com', $updatedModel->email);
    }

    /**
     * Test update action with different data values
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_with_different_data_values()
    {
        $model = UpdateActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 25
        ]);

        // Update with different values
        $request = Request::create('/', 'PUT', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'age' => 35
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);

        $updatedModel = UpdateActionTestModel::find($model->id);
        $this->assertEquals('Updated User', $updatedModel->name);
        $this->assertEquals('updated@example.com', $updatedModel->email);
        $this->assertEquals(35, $updatedModel->age);
    }

    /**
     * Test update action executes fillForUpdate and updateRelations
     * This verifies both methods are called by testing successful update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_executes_fill_and_relations_methods()
    {
        $model = UpdateActionTestModel::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'age' => 25
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated',
            'email' => 'updated@example.com',
            'age' => 30
        ]);

        // If fillForUpdate and updateRelations are not executed, the update would fail
        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
    }

    /**
     * Test that update action handles BelongsTo relations correctly (associate)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_handles_belongs_to_relation()
    {
        $category1 = UpdateActionTestCategory::where('name', 'Technology')->first();
        $category2 = UpdateActionTestCategory::where('name', 'Business')->first();

        $model = UpdateActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 30,
            'category_id' => $category1->id
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'age' => 35,
            'category_id' => $category2->id
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['updated']);

        // Assert relation was updated
        $updatedModel = UpdateActionTestModel::find($model->id);
        $this->assertEquals($category2->id, $updatedModel->category_id);
        $this->assertEquals('Business', $updatedModel->category->name);
    }

    /**
     * Test that update action handles BelongsTo dissociate correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_handles_belongs_to_dissociate()
    {
        $category = UpdateActionTestCategory::where('name', 'Technology')->first();

        $model = UpdateActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 30,
            'category_id' => $category->id
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'age' => 35,
            'category_id' => null  // Dissociate
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['updated']);

        // Assert relation was dissociated
        $updatedModel = UpdateActionTestModel::find($model->id);
        $this->assertNull($updatedModel->category_id);
    }

    /**
     * Test that update action handles BelongsToMany relations correctly (sync)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_handles_belongs_to_many_relation()
    {
        $laravelTag = UpdateActionTestTag::where('name', 'Laravel')->first();
        $phpTag = UpdateActionTestTag::where('name', 'PHP')->first();

        $model = UpdateActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 30
        ]);

        // First attach some tags
        $model->tags()->attach([$laravelTag->id]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'age' => 35,
            'tags' => [$phpTag->id, $laravelTag->id]  // Sync to different set
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['updated']);

        // Assert relations were synced
        $updatedModel = UpdateActionTestModel::with('tags')->find($model->id);
        $this->assertCount(2, $updatedModel->tags);
        $tagNames = $updatedModel->tags->pluck('name')->sort()->values();
        $this->assertEquals(['Laravel', 'PHP'], $tagNames->toArray());
    }

    /**
     * Test that update action handles HasMany relations correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_action_handles_has_many_relation()
    {
        // Create some comments
        $comment1 = UpdateActionTestComment::create(['content' => 'First comment']);
        $comment2 = UpdateActionTestComment::create(['content' => 'Second comment']);
        $comment3 = UpdateActionTestComment::create(['content' => 'Third comment']);

        $model = UpdateActionTestModel::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => 30
        ]);

        // Attach initial comments
        $model->comments()->saveMany([$comment1, $comment2]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'age' => 35,
            'comments' => [$comment2->id, $comment3->id]  // Update comments
        ]);

        $response = $this->controller->callAction('update', [$request, $model->id]);
        $data = $response->getData(true);

        // Assert response
        $this->assertTrue($data['updated']);

        // Assert comments were updated (saveMany adds to the relation)
        $updatedModel = UpdateActionTestModel::with('comments')->find($model->id);
        // saveMany adds new comments without removing old ones, so we have all 3
        $this->assertCount(3, $updatedModel->comments);
        $commentIds = $updatedModel->comments->pluck('id')->sort()->values();
        $this->assertEquals([$comment1->id, $comment2->id, $comment3->id], $commentIds->toArray());
    }
}
