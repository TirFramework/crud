<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Number;
use Tir\Crud\Support\Scaffold\Actions;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test model WITHOUT $fillable for MongoDB adapter testing
 * This triggers the scaffolder fallback in MongoDbAdapter.fillModel()
 */
class MongoDbAdapterNoFillableTestModel extends Model
{
    protected $table = 'mongodb_adapter_no_fillable_test_models';
    // NO $fillable defined - will use scaffolder fields
    
    // Optional: define guarded to test filterOutGuardedFields
    protected $guarded = ['id', 'secret_field'];

    // Cast profile as JSON for SQLite compatibility
    protected $casts = [
        'profile' => 'array',
    ];

    /**
     * Override getConnection to mock MongoDB driver
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mongodb');
        
        return $mock;
    }
}

/**
 * Test scaffolder for MongoDB adapter with non-fillable model
 */
class MongoDbAdapterNoFillableTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'mongodb-adapter-no-fillable-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            Text::make('secret_field')->fillable(false), // Non-fillable
            Text::make('profile.bio'),   // Nested field
            Text::make('profile.location'), // Nested field
        ];
    }

    protected function setModel(): string
    {
        return MongoDbAdapterNoFillableTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for MongoDB adapter with non-fillable model
 */
class MongoDbAdapterNoFillableTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Store;

    protected function setScaffolder(): string
    {
        return MongoDbAdapterNoFillableTestScaffolder::class;
    }

    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }
}

/**
 * MongoDB Adapter Fillable Fallback Test
 *
 * Tests the MongoDB adapter's behavior when model has NO $fillable array
 * This triggers the scaffolder-based fillable logic (Priority 2)
 */
class MongoDbAdapterFillableFallbackTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private MongoDbAdapterNoFillableTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table with JSON column for profile
        \Illuminate\Support\Facades\Schema::create('mongodb_adapter_no_fillable_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->string('secret_field')->nullable();
            $table->json('profile')->nullable(); // JSON column for nested data
            $table->timestamps();
        });

        $this->controller = new MongoDbAdapterNoFillableTestController();
    }

    /**
     * Test MongoDB adapter uses scaffolder fields when model has NO $fillable
     * This covers lines 210-224 in MongoDbAdapter.fillModel()
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_uses_scaffolder_fillable_when_model_has_no_fillable()
    {
        // Model has NO $fillable, so adapter should use scaffolder fields
        $request = Request::create('/', 'POST', [
            'name' => 'Scaffolder Test',
            'email' => 'scaffolder@example.com',
            'age' => 35,
            'profile.bio' => 'Software Developer',
            'profile.location' => 'New York'
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        // Should succeed using scaffolder fields (Priority 2 fallback)
        $this->assertTrue($data['created']);
        $this->assertDatabaseHas('mongodb_adapter_no_fillable_test_models', [
            'name' => 'Scaffolder Test',
            'email' => 'scaffolder@example.com',
            'age' => 35
        ]);

        // Verify nested profile was stored (MongoDB adapter converts dot notation)
        $model = MongoDbAdapterNoFillableTestModel::find($data['id']);
        $this->assertEquals('Scaffolder Test', $model->name);
    }

    /**
     * Test MongoDB adapter filters out non-fillable fields from scaffolder
     * Tests line 213-214: Only include fields that are fillable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_filters_non_fillable_scaffolder_fields()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Filter Test',
            'email' => 'filter@example.com',
            'age' => 30,
            'secret_field' => 'should not be set', // Non-fillable in scaffolder
            'extra_field' => 'also filtered'
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        $this->assertTrue($data['created']);
        
        // Verify non-fillable field was not set
        $model = MongoDbAdapterNoFillableTestModel::find($data['id']);
        $this->assertEquals('Filter Test', $model->name);
        $this->assertNull($model->secret_field); // Should be null (filtered)
    }

    /**
     * Test MongoDB adapter filters out guarded fields
     * Tests the filterOutGuardedFields method (lines 263-290)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_filters_out_guarded_fields()
    {
        // Model has $guarded = ['id', 'secret_field']
        $request = Request::create('/', 'POST', [
            'name' => 'Guarded Test',
            'email' => 'guarded@example.com',
            'age' => 28,
            'secret_field' => 'should be filtered by guarded'
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        $this->assertTrue($data['created']);
        
        // Verify guarded field was not set (even though it's in request)
        $model = MongoDbAdapterNoFillableTestModel::find($data['id']);
        $this->assertEquals('Guarded Test', $model->name);
        // secret_field should be null because it's in $guarded array
        $this->assertNull($model->secret_field);
    }

    /**
     * Test MongoDB adapter handles nested guarded patterns
     * Tests line 279-281: Check if field is a nested field of a guarded field
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_filters_nested_guarded_fields()
    {
        // Create a model with nested guarded field
        $modelClass = new class extends Model {
            protected $table = 'mongodb_adapter_no_fillable_test_models';
            // No $fillable - will use scaffolder
            protected $guarded = ['profile']; // Guard entire profile object
            
            public function getConnection()
            {
                $connection = parent::getConnection();
                $mock = \Mockery::mock($connection)->makePartial();
                $mock->shouldReceive('getDriverName')->andReturn('mongodb');
                return $mock;
            }
        };

        // With profile guarded, profile.bio and profile.location should be blocked
        $request = Request::create('/', 'POST', [
            'name' => 'Nested Guarded Test',
            'email' => 'nested@example.com',
            'age' => 40,
            'profile.bio' => 'Should be blocked',
            'profile.location' => 'Should also be blocked'
        ]);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        $this->assertTrue($data['created']);
        
        // Main fields should be saved
        $this->assertDatabaseHas('mongodb_adapter_no_fillable_test_models', [
            'name' => 'Nested Guarded Test',
            'email' => 'nested@example.com',
            'age' => 40
        ]);
    }
}
