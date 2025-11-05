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
 * Test model for ProcessRequest MySQL adapter testing
 */
class ProcessRequestMySqlTestModel extends Model
{
    protected $table = 'process_request_mysql_test_models';
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
}

/**
 * Test scaffolder for ProcessRequest MySQL adapter testing
 */
class ProcessRequestMySqlTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'process-request-mysql-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
            Text::make('hidden_field')->fillable(false), // Non-fillable field
        ];
    }

    protected function setModel(): string
    {
        return ProcessRequestMySqlTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for ProcessRequest MySQL adapter testing
 */
class ProcessRequestMySqlTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\ProcessRequest;

    protected function setScaffolder(): string
    {
        return ProcessRequestMySqlTestScaffolder::class;
    }

    // Expose private methods for testing
    public function testProcessRequest(Request $request)
    {
        return $this->processRequest($request);
    }
}

/**
 * ProcessRequest MySQL Adapter Integration Test
 *
 * Tests the processRequest method with MySQL database adapter
 * MySQL adapter keeps flat array structure and doesn't convert dot notation
 */
class ProcessRequestMySqlTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private ProcessRequestMySqlTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('process_request_mysql_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->timestamps();
        });

        $this->controller = new ProcessRequestMySqlTestController();
    }

    /**
     * Test MySQL adapter keeps flat array structure in processRequest
     * MySQL should NOT convert dot notation to nested arrays
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_process_request_keeps_flat_array_structure()
    {
        // Create request with flat structure
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // MySQL adapter should keep the flat structure
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        $this->assertEquals('john@example.com', $processedRequest->input('email'));
        $this->assertEquals(25, $processedRequest->input('age'));
        
        // All data should be at root level (not nested)
        $allData = $processedRequest->all();
        $this->assertIsString($allData['name'] ?? null);
        $this->assertIsString($allData['email'] ?? null);
        $this->assertIsInt($allData['age'] ?? null);
    }

    /**
     * Test MySQL adapter filters non-fillable fields in processRequest
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_process_request_filters_non_fillable_fields()
    {
        // Create request with both fillable and non-fillable fields
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'hidden_field' => 'should be filtered out',
            'extra_field' => 'also filtered out'
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // Assert that only fillable fields are kept
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        $this->assertEquals('john@example.com', $processedRequest->input('email'));
        $this->assertEquals(25, $processedRequest->input('age'));

        // Assert that non-fillable fields are filtered out
        $this->assertNull($processedRequest->input('hidden_field'));
        $this->assertNull($processedRequest->input('extra_field'));
    }

    /**
     * Test MySQL adapter does NOT convert dot notation to nested arrays
     * This is key difference from MongoDB adapter
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_process_request_does_not_convert_dot_notation()
    {
        // Create request with dot notation keys (which would be filtered out as not in scaffold)
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'address.street' => '123 Main St', // Not in scaffold, should be filtered
            'address.city' => 'New York'       // Not in scaffold, should be filtered
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // MySQL adapter should keep fillable fields as-is
        $this->assertEquals('John Doe', $processedRequest->input('name'));
        $this->assertEquals('john@example.com', $processedRequest->input('email'));
        $this->assertEquals(25, $processedRequest->input('age'));
        
        // Dot notation fields should be filtered out (not in scaffold)
        $this->assertNull($processedRequest->input('address.street'));
        $this->assertNull($processedRequest->input('address.city'));
        
        // And should NOT be converted to nested structure
        $this->assertNull($processedRequest->input('address'));
    }

    /**
     * Test MySQL adapter handles empty request
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_process_request_handles_empty_request()
    {
        // Create empty request
        $request = Request::create('/', 'POST', []);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // Should return empty array
        $this->assertEmpty($processedRequest->all());
    }

    /**
     * Test MySQL adapter preserves data types
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_process_request_preserves_data_types()
    {
        // Create request with different data types
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',      // string
            'email' => 'john@test.com', // string
            'age' => 25                 // integer
        ]);

        // Process the request
        $processedRequest = $this->controller->testProcessRequest($request);

        // MySQL adapter should preserve data types
        $this->assertIsString($processedRequest->input('name'));
        $this->assertIsString($processedRequest->input('email'));
        $this->assertIsInt($processedRequest->input('age'));
    }
}
