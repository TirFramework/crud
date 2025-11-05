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
 * Test model WITHOUT $fillable - uses scaffolder fallback
 */
class MySqlAdapterNoFillableTestModel extends Model
{
    protected $table = 'mysql_adapter_no_fillable_test_models';
    // NO $fillable property defined - should use scaffolder fields
    
    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }
}

/**
 * Test model WITH $fillable - standard behavior
 */
class MySqlAdapterWithFillableTestModel extends Model
{
    protected $table = 'mysql_adapter_with_fillable_test_models';
    protected $fillable = ['name', 'email', 'age'];
    
    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }
}

/**
 * Test model WITH $guarded - tests guarded field filtering
 */
class MySqlAdapterWithGuardedTestModel extends Model
{
    protected $table = 'mysql_adapter_with_guarded_test_models';
    protected $guarded = ['admin_field', 'protected_field'];
    
    protected $casts = [
        'metadata' => 'array',
    ];
    
    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }
}

/**
 * Test model WITH $guarded = ['*'] - tests universal guard
 */
class MySqlAdapterGuardAllTestModel extends Model
{
    protected $table = 'mysql_adapter_guard_all_test_models';
    protected $guarded = ['*'];
    
    /**
     * Override getConnection to mock MySQL driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mysql');
        
        return $mock;
    }
}

/**
 * Scaffolder for model without fillable
 */
class MySqlAdapterNoFillableTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'mysql-adapter-no-fillable-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer']),
            Text::make('tags.0'),
            Text::make('tags.1'),
            Text::make('metadata.key1'),
            Text::make('hidden_field')->fillable(false), // Non-fillable
        ];
    }

    protected function setModel(): string
    {
        return MySqlAdapterNoFillableTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Scaffolder for model with guarded fields
 */
class MySqlAdapterWithGuardedTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'mysql-adapter-with-guarded-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string']),
            Text::make('email')->rules(['required', 'email']),
            Text::make('age')->rules(['required', 'integer']),
            Text::make('admin_field'), // Should be guarded
            Text::make('protected_field'), // Should be guarded
            Text::make('metadata.safe_key'),
        ];
    }

    protected function setModel(): string
    {
        return MySqlAdapterWithGuardedTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Controller for model without fillable
 */
class MySqlAdapterNoFillableTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Store;

    protected function setScaffolder(): string
    {
        return MySqlAdapterNoFillableTestScaffolder::class;
    }
}

/**
 * Controller for model with guarded
 */
class MySqlAdapterWithGuardedTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Store;

    protected function setScaffolder(): string
    {
        return MySqlAdapterWithGuardedTestScaffolder::class;
    }
}

/**
 * MySQL Adapter Fillable Fallback Test
 *
 * Tests the MySQL adapter's fillModel method when model doesn't have $fillable
 * This covers lines 303-334 (scaffolder fallback) and filterOutGuardedFields method
 */
class MySqlAdapterFillableFallbackTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create table for model without fillable
        \Illuminate\Support\Facades\Schema::create('mysql_adapter_no_fillable_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Create table for model with guarded
        \Illuminate\Support\Facades\Schema::create('mysql_adapter_with_guarded_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->string('admin_field')->nullable();
            $table->string('protected_field')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Create table for model with guard all
        \Illuminate\Support\Facades\Schema::create('mysql_adapter_guard_all_test_models', function ($table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Test MySQL adapter uses scaffolder fields when model has NO $fillable
     * This tests the scaffolder fallback mechanism (lines 303-334)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_uses_scaffolder_fields_when_model_has_no_fillable()
    {
        $controller = new MySqlAdapterNoFillableTestController();

        // Model has NO $fillable, so adapter should use scaffolder fields
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'hidden_field' => 'should be filtered', // Not fillable in scaffolder
            'extra_field' => 'should be filtered'   // Not in scaffolder
        ]);

        $response = $controller->store($request);
        $data = $response->getData(true);

        $this->assertTrue($data['created']);
        
        // Verify data was stored using scaffolder fields
        $model = MySqlAdapterNoFillableTestModel::find($data['id']);
        $this->assertEquals('John Doe', $model->name);
        $this->assertEquals('john@example.com', $model->email);
        $this->assertEquals(25, $model->age);
        
        // Verify hidden_field was NOT saved (not fillable in scaffolder)
        $this->assertDatabaseMissing('mysql_adapter_no_fillable_test_models', [
            'id' => $data['id'],
            'hidden_field' => 'should be filtered'
        ]);
    }

    /**
     * Test MySQL adapter handles nested fields with scaffolder fallback
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_handles_nested_fields_with_scaffolder_fallback()
    {
        $controller = new MySqlAdapterNoFillableTestController();

        $request = Request::create('/', 'POST', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'age' => 30,
            'tags.0' => 'tag1',
            'tags.1' => 'tag2',
            'metadata.key1' => 'value1'
        ]);

        $response = $controller->store($request);
        $data = $response->getData(true);

        $this->assertTrue($data['created']);
        
        $model = MySqlAdapterNoFillableTestModel::find($data['id']);
        $this->assertEquals('Jane Doe', $model->name);
        $this->assertEquals('jane@example.com', $model->email);
        $this->assertEquals(30, $model->age);
    }

    /**
     * Test MySQL adapter filters out guarded fields
     * This tests the filterOutGuardedFields method (lines 353-372)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_filters_out_guarded_fields()
    {
        $controller = new MySqlAdapterWithGuardedTestController();

        // Model has $guarded = ['admin_field', 'protected_field']
        $request = Request::create('/', 'POST', [
            'name' => 'Guard Test',
            'email' => 'guard@example.com',
            'age' => 35,
            'admin_field' => 'should be blocked',      // Guarded
            'protected_field' => 'should be blocked',  // Guarded
            'metadata.safe_key' => 'should be allowed' // Not guarded
        ]);

        $response = $controller->store($request);
        $data = $response->getData(true);

        $this->assertTrue($data['created']);
        
        // Verify allowed fields were saved
        $model = MySqlAdapterWithGuardedTestModel::find($data['id']);
        $this->assertEquals('Guard Test', $model->name);
        $this->assertEquals('guard@example.com', $model->email);
        $this->assertEquals(35, $model->age);
        
        // Verify guarded fields were NOT saved
        $this->assertNull($model->admin_field);
        $this->assertNull($model->protected_field);
    }

    /**
     * Test MySQL adapter handles nested guarded fields
     * Tests that guarded=['profile'] blocks 'profile.eyes_color', 'profile.height', etc.
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_filters_nested_guarded_fields()
    {
        // Create a custom model class with metadata guarded
        $model = new class extends Model {
            protected $table = 'mysql_adapter_with_guarded_test_models';
            protected $guarded = ['metadata']; // Guard entire metadata object
            
            protected $casts = [
                'metadata' => 'array',
            ];
            
            public function getConnection()
            {
                $connection = parent::getConnection();
                $mock = \Mockery::mock($connection)->makePartial();
                $mock->shouldReceive('getDriverName')->andReturn('mysql');
                return $mock;
            }
        };
        
        $adapter = new \Tir\Crud\Support\Database\Adapters\MySqlAdapter();
        
        $data = [
            'name' => 'Nested Guard Test',
            'email' => 'nested@example.com',
            'age' => 40,
            'metadata' => ['safe_key' => 'should be blocked'], // Guarded
        ];
        
        $scaffolderFields = [
            (object)['name' => 'name', 'request' => ['name'], 'fillable' => true],
            (object)['name' => 'email', 'request' => ['email'], 'fillable' => true],
            (object)['name' => 'age', 'request' => ['age'], 'fillable' => true],
            (object)['name' => 'metadata', 'request' => ['metadata'], 'fillable' => true],
        ];
        
        $filledModel = $adapter->fillModel($model, $data, $scaffolderFields);
        
        // Allowed fields should be set
        $this->assertEquals('Nested Guard Test', $filledModel->name);
        $this->assertEquals('nested@example.com', $filledModel->email);
        $this->assertEquals(40, $filledModel->age);
        
        // Save and verify metadata field was blocked
        $filledModel->save();
        $savedModel = get_class($model)::find($filledModel->id);
        
        // metadata should not be saved because it's guarded
        $this->assertNull($savedModel->metadata);
    }

    /**
     * Test MySQL adapter handles $guarded = ['*'] with explicit fillable
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_handles_guard_all_with_fillable()
    {
        $model = new MySqlAdapterGuardAllTestModel();
        $model->fillable(['name', 'email']); // Set explicit fillable

        // Manually test fillModel behavior
        $adapter = new \Tir\Crud\Support\Database\Adapters\MySqlAdapter();
        
        $data = [
            'name' => 'Guard All Test',
            'email' => 'guardall@example.com',
            'extra_field' => 'should be blocked'
        ];
        
        $scaffolderFields = [];
        
        $filledModel = $adapter->fillModel($model, $data, $scaffolderFields);
        
        // With guard all and explicit fillable, only fillable fields should be set
        $this->assertEquals('Guard All Test', $filledModel->name);
        $this->assertEquals('guardall@example.com', $filledModel->email);
    }

    /**
     * Test MySQL adapter with empty scaffolder fields falls back gracefully
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_with_empty_scaffolder_fields()
    {
        $model = new MySqlAdapterWithFillableTestModel();
        $adapter = new \Tir\Crud\Support\Database\Adapters\MySqlAdapter();
        
        $data = [
            'name' => 'Empty Scaffolder Test',
            'email' => 'empty@example.com',
            'age' => 45
        ];
        
        // Empty scaffolder fields - should use model's fillable
        $scaffolderFields = [];
        
        $filledModel = $adapter->fillModel($model, $data, $scaffolderFields);
        
        $this->assertEquals('Empty Scaffolder Test', $filledModel->name);
        $this->assertEquals('empty@example.com', $filledModel->email);
        $this->assertEquals(45, $filledModel->age);
    }
}
