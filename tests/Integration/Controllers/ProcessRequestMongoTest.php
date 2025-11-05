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
 * Test model for ProcessRequest MongoDB adapter testing
 */
class ProcessRequestMongoTestModel extends Model
{
    protected $table = 'process_request_mongo_test_models';
    protected $fillable = ['name', 'email', 'age', 'address', 'tags', 'metadata'];

    /**
     * Override getConnection to mock MongoDB driver for adapter testing
     */
    public function getConnection()
    {
        $connection = parent::getConnection();
        
        // Create a partial mock that wraps the real connection
        $mock = \Mockery::mock($connection)->makePartial();
        $mock->shouldReceive('getDriverName')->andReturn('mongodb');
        
        return $mock;
    }
}

/**
 * Test scaffolder for ProcessRequest MongoDB adapter testing
 */
class ProcessRequestMongoTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'process-request-mongo-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            // MongoDB supports nested fields
            Text::make('address.street'),
            Text::make('address.city'),
            Text::make('address.country'),
            Text::make('tags.0'),
            Text::make('tags.1'),
            Text::make('tags.2'),
            Text::make('hidden_field')->fillable(false), // Non-fillable field
        ];
    }

    protected function setModel(): string
    {
        return ProcessRequestMongoTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for ProcessRequest MongoDB adapter testing
 */
class ProcessRequestMongoTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\ProcessRequest;

    protected function setScaffolder(): string
    {
        return ProcessRequestMongoTestScaffolder::class;
    }

    // Expose private methods for testing
    public function testProcessRequest(Request $request)
    {
        return $this->processRequest($request);
    }
}

/**
 * ProcessRequest MongoDB Adapter Integration Test
 *
 * Tests the processRequest method with MongoDB database adapter
 * MongoDB adapter converts dot notation to nested array structure using Arr::undot()
 */
class ProcessRequestMongoTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private ProcessRequestMongoTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('process_request_mongo_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->text('address')->nullable();
            $table->text('tags')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        $this->controller = new ProcessRequestMongoTestController();
    }

    /**
     * Test MongoDB adapter converts dot notation to nested arrays using Arr::undot()
     * This is the KEY difference from MySQL adapter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_process_request_converts_dot_notation_to_nested_arrays()
    {
        // Create request with dot notation (flat structure)
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'address.street' => '123 Main St',
            'address.city' => 'New York',
            'address.country' => 'USA'
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // MongoDB adapter should convert dot notation to nested structure
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        $this->assertEquals('john@example.com', $processedRequest->input('email'));
        $this->assertEquals(25, $processedRequest->input('age'));
        
        // The key test: address should now be a nested array
        $address = $processedRequest->input('address');
        $this->assertIsArray($address);
        $this->assertEquals('123 Main St', $address['street']);
        $this->assertEquals('New York', $address['city']);
        $this->assertEquals('USA', $address['country']);
        
        // With nested structure, accessing via dot notation should still work in Laravel
        // but the data structure itself is nested
        $this->assertEquals('123 Main St', $processedRequest->input('address.street'));
        $this->assertEquals('New York', $processedRequest->input('address.city'));
        $this->assertEquals('USA', $processedRequest->input('address.country'));
    }

    /**
     * Test MongoDB adapter handles indexed array fields (tags.0, tags.1, etc)
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_process_request_converts_indexed_fields_to_arrays()
    {
        // Create request with indexed array fields
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'tags.0' => 'php',
            'tags.1' => 'laravel',
            'tags.2' => 'mongodb'
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // MongoDB adapter should convert indexed fields to array
        $tags = $processedRequest->input('tags');
        $this->assertIsArray($tags);
        $this->assertEquals('php', $tags[0]);
        $this->assertEquals('laravel', $tags[1]);
        $this->assertEquals('mongodb', $tags[2]);
        
        // With nested structure, accessing via dot notation should still work in Laravel
        $this->assertEquals('php', $processedRequest->input('tags.0'));
        $this->assertEquals('laravel', $processedRequest->input('tags.1'));
        $this->assertEquals('mongodb', $processedRequest->input('tags.2'));
    }

    /**
     * Test MongoDB adapter filters non-fillable fields before conversion
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_process_request_filters_non_fillable_fields()
    {
        // Create request with both fillable and non-fillable fields
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'address.street' => '123 Main St',
            'hidden_field' => 'should be filtered out',
            'extra_field' => 'also filtered out'
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // Assert that only fillable fields are kept
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        $this->assertEquals('john@example.com', $processedRequest->input('email'));
        $this->assertEquals(25, $processedRequest->input('age'));
        
        // Address should be converted to nested
        $address = $processedRequest->input('address');
        $this->assertIsArray($address);
        $this->assertEquals('123 Main St', $address['street']);

        // Assert that non-fillable fields are filtered out
        $this->assertNull($processedRequest->input('hidden_field'));
        $this->assertNull($processedRequest->input('extra_field'));
    }

    /**
     * Test MongoDB adapter handles deeply nested structures
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_process_request_handles_deeply_nested_structures()
    {
        // Create request with deeply nested dot notation
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'address.street' => '123 Main St',
            'address.city' => 'New York'
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // MongoDB adapter should convert all levels
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        
        // Check nested structure
        $address = $processedRequest->input('address');
        $this->assertIsArray($address);
        $this->assertArrayHasKey('street', $address);
        $this->assertArrayHasKey('city', $address);
        $this->assertEquals('123 Main St', $address['street']);
        $this->assertEquals('New York', $address['city']);
    }

    /**
     * Test MongoDB adapter handles mixed flat and nested fields
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_process_request_handles_mixed_flat_and_nested_fields()
    {
        // Create request with both flat and nested fields
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',           // flat
            'email' => 'john@example.com',  // flat
            'age' => 25,                    // flat
            'address.street' => '123 Main St', // nested
            'address.city' => 'New York',      // nested
            'tags.0' => 'php',              // array
            'tags.1' => 'mongodb'           // array
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // Flat fields should remain flat
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        $this->assertEquals('john@example.com', $processedRequest->input('email'));
        $this->assertEquals(25, $processedRequest->input('age'));
        
        // Nested fields should be converted to nested arrays
        $address = $processedRequest->input('address');
        $this->assertIsArray($address);
        $this->assertEquals('123 Main St', $address['street']);
        $this->assertEquals('New York', $address['city']);
        
        // Indexed fields should be converted to arrays
        $tags = $processedRequest->input('tags');
        $this->assertIsArray($tags);
        $this->assertEquals('php', $tags[0]);
        $this->assertEquals('mongodb', $tags[1]);
    }

    /**
     * Test MongoDB adapter handles empty request
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mongodb_process_request_handles_empty_request()
    {
        // Create empty request
        $request = Request::create('/', 'POST', []);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // Should return empty array
        $this->assertEmpty($processedRequest->all());
    }
}
