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
 * Test model for Update action MongoDB integration testing
 */
class UpdateActionMongoTestModel extends Model
{
    protected $table = 'update_action_mongo_test_models';
    protected $fillable = ['name', 'email', 'age', 'address', 'tags'];

    protected $casts = [
        'address' => 'array',
        'tags' => 'array',
    ];

    /**
     * Override getConnection to mock MongoDB driver for adapter testing
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
 * Test scaffolder for Update action MongoDB integration testing
 */
class UpdateActionMongoTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'update-action-mongo-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            Text::make('address.street'),
            Text::make('address.city'),
            Text::make('address.country'),
            Text::make('tags.0'),
            Text::make('tags.1'),
            Text::make('tags.2'),
        ];
    }

    protected function setModel(): string
    {
        return UpdateActionMongoTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for Update action MongoDB integration testing
 */
class UpdateActionMongoTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Update;

    protected function setScaffolder(): string
    {
        return UpdateActionMongoTestScaffolder::class;
    }

    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }
}

/**
 * Update Action MongoDB Integration Test
 *
 * Tests the complete update action flow with MongoDB database adapter
 */
class UpdateActionMongoTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private UpdateActionMongoTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('update_action_mongo_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->json('address')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });

        $this->controller = new UpdateActionMongoTestController();
    }

    /**
     * Test MongoDB adapter converts dot notation to nested arrays during update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_converts_dot_notation_to_nested_arrays_on_update()
    {
        // Create initial record
        $model = UpdateActionMongoTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30,
            'address' => ['street' => 'Old Street', 'city' => 'Old City', 'country' => 'Old Country']
        ]);

        // Update with dot notation (flat structure)
        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'age' => 35,
            'address.street' => 'New Street',
            'address.city' => 'New City',
            'address.country' => 'New Country'
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // MongoDB adapter should convert to nested structure
        $updatedModel = UpdateActionMongoTestModel::find($model->id);
        $this->assertEquals('Updated Name', $updatedModel->name);
        $this->assertEquals('updated@example.com', $updatedModel->email);
        $this->assertEquals(35, $updatedModel->age);
        
        // Address should be nested array
        $this->assertIsArray($updatedModel->address);
        $this->assertEquals('New Street', $updatedModel->address['street']);
        $this->assertEquals('New City', $updatedModel->address['city']);
        $this->assertEquals('New Country', $updatedModel->address['country']);
    }

    /**
     * Test MongoDB adapter uses direct property assignment during update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_uses_direct_property_assignment_on_update()
    {
        $model = UpdateActionMongoTestModel::create([
            'name' => 'Before Direct',
            'email' => 'before@example.com',
            'age' => 25
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'After Direct',
            'email' => 'after@example.com',
            'age' => 30
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // MongoDB adapter uses direct property assignment
        $updatedModel = UpdateActionMongoTestModel::find($model->id);
        $this->assertEquals('After Direct', $updatedModel->name);
        $this->assertEquals('after@example.com', $updatedModel->email);
        $this->assertEquals(30, $updatedModel->age);
    }

    /**
     * Test MongoDB adapter handles indexed array fields during update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_handles_indexed_array_fields_on_update()
    {
        $model = UpdateActionMongoTestModel::create([
            'name' => 'Array Test',
            'email' => 'array@example.com',
            'age' => 28,
            'tags' => ['old1', 'old2']
        ]);

        // Update with indexed array fields
        $request = Request::create('/', 'PUT', [
            'name' => 'Array Test Updated',
            'email' => 'array@example.com',
            'age' => 29,
            'tags.0' => 'new1',
            'tags.1' => 'new2',
            'tags.2' => 'new3'
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // Tags should be converted to array
        $updatedModel = UpdateActionMongoTestModel::find($model->id);
        $this->assertEquals('Array Test Updated', $updatedModel->name);
        $this->assertIsArray($updatedModel->tags);
        $this->assertEquals('new1', $updatedModel->tags[0]);
        $this->assertEquals('new2', $updatedModel->tags[1]);
        $this->assertEquals('new3', $updatedModel->tags[2]);
    }

    /**
     * Test MongoDB adapter filters fillable fields during update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_adapter_filters_fillable_fields_on_update()
    {
        $model = UpdateActionMongoTestModel::create([
            'name' => 'Filter Test',
            'email' => 'filter@example.com',
            'age' => 35
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Filter',
            'email' => 'updated@example.com',
            'age' => 40,
            'extra_field' => 'should be filtered',
            'nested.extra' => 'also filtered'
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // Verify only fillable fields were updated
        $updatedModel = UpdateActionMongoTestModel::find($model->id);
        $this->assertEquals('Updated Filter', $updatedModel->name);
        $this->assertEquals('updated@example.com', $updatedModel->email);
        $this->assertEquals(40, $updatedModel->age);
        
        // Extra fields should not exist
        $this->assertFalse(isset($updatedModel->extra_field));
        $this->assertFalse(isset($updatedModel->nested));
    }
}
