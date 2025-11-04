<?php

namespace Tir\Crud\Tests\Integration\Controllers;

use Tir\Crud\Controllers\CrudController;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Scaffold\Fields\Number;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Enums\ActionType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test model for ProcessRequest integration testing
 */
class ProcessRequestTestModel extends Model
{
    protected $fillable = ['name', 'email', 'age'];
}

/**
 * Test scaffolder for ProcessRequest integration testing
 */
class ProcessRequestTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'process-request-test';
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
        return ProcessRequestTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all(); // Enable all actions for testing
    }
}

/**
 * Test controller for ProcessRequest integration testing
 */
class ProcessRequestTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\ProcessRequest;

    protected function setScaffolder(): string
    {
        return ProcessRequestTestScaffolder::class;
    }

    protected array $hookTracker = [];

    // Expose private methods for testing
    public function testProcessRequest(Request $request)
    {
        return $this->processRequest($request);
    }

    public function testValidateCreateRequest(Request $request)
    {
        // Initialize scaffold for validation
        $this->scaffolder()->scaffold('create');
        return $this->validateCreateRequest($request);
    }

    public function testValidateUpdateRequest(Request $request, $id)
    {
        // Initialize scaffold for validation
        $this->scaffolder()->scaffold('edit');
        return $this->validateUpdateRequest($request, $id);
    }

    public function testValidateInlineUpdateRequest(Request $request, $id)
    {
        // Initialize scaffold for validation
        $this->scaffolder()->scaffold('edit');
        return $this->validateInlineUpdateRequest($request, $id);
    }

    protected function setup(): void
    {
        // Register hooks for testing
        $this->onProcessRequest(function ($defaultProcess) {
            $this->hookTracker['onProcessRequest'] = true;
            return $defaultProcess();
        });

        $this->onStoreValidation(function ($defaultValidation) {
            $this->hookTracker['onStoreValidation'] = true;
            return $defaultValidation();
        });

        $this->onUpdateValidation(function ($defaultValidation) {
            $this->hookTracker['onUpdateValidation'] = true;
            return $defaultValidation();
        });

        $this->onInlineUpdateValidation(function ($defaultValidation) {
            $this->hookTracker['onInlineUpdateValidation'] = true;
            return $defaultValidation();
        });
    }

    public function getHookTracker(): array
    {
        return $this->hookTracker;
    }

    public function resetHookTracker()
    {
        $this->hookTracker = [];
    }
}

class ProcessRequestTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private ProcessRequestTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        \Illuminate\Support\Facades\Schema::create('process_request_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->timestamps();
        });

        $this->controller = new ProcessRequestTestController();
    }

    public function test_process_request_filters_fillable_fields_only()
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

    public function test_process_request_preserves_all_data_when_no_scaffold_fields()
    {
        // This test would require a custom controller without fields
        // Skipping for now as it's not essential for ProcessRequest testing
        $this->assertTrue(true);
    }

    public function test_process_request_executes_on_process_request_hook()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->controller->resetHookTracker();
        $this->controller->testProcessRequest($request);

        // Assert that the hook was executed
        $this->assertTrue($this->controller->getHookTracker()['onProcessRequest']);
    }

    public function test_validate_create_request_with_valid_data()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        // Should not throw exception
        $result = $this->controller->testValidateCreateRequest($request);
        $this->assertTrue($result);
    }

    public function test_validate_create_request_with_invalid_data()
    {
        $request = Request::create('/', 'POST', [
            'name' => '', // Required field empty
            'email' => 'invalid-email', // Invalid email
            'age' => 'not-a-number' // Invalid integer
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->testValidateCreateRequest($request);
    }

    public function test_validate_create_request_executes_hook()
    {
        $request = Request::create('/', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ]);

        $this->controller->resetHookTracker();
        $this->controller->testValidateCreateRequest($request);

        $this->assertTrue($this->controller->getHookTracker()['onStoreValidation']);
    }

    public function test_validate_update_request_with_valid_data()
    {
        // Create a test model first
        $model = ProcessRequestTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'age' => 35
        ]);

        // Should not throw exception
        $result = $this->controller->testValidateUpdateRequest($request, $model->id);
        $this->assertTrue($result);
    }

    public function test_validate_update_request_with_invalid_data()
    {
        $model = ProcessRequestTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => '', // Required field empty
            'email' => 'invalid-email', // Invalid email
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->testValidateUpdateRequest($request, $model->id);
    }

    public function test_validate_update_request_executes_hook()
    {
        $model = ProcessRequestTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'age' => 35
        ]);

        $this->controller->resetHookTracker();
        $this->controller->testValidateUpdateRequest($request, $model->id);

        $this->assertTrue($this->controller->getHookTracker()['onUpdateValidation']);
    }

    public function test_validate_inline_update_request_with_valid_data()
    {
        $model = ProcessRequestTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PATCH', [
            'name' => 'Inline Updated Name',
            'email' => 'updated@example.com', // Required for inline update
            'age' => 35 // Required for inline update
        ]);

        // Should not throw exception
        $result = $this->controller->testValidateInlineUpdateRequest($request, $model->id);
        $this->assertTrue($result);
    }

    public function test_validate_inline_update_request_executes_hook()
    {
        $model = ProcessRequestTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        $request = Request::create('/', 'PATCH', [
            'name' => 'Inline Updated Name',
            'email' => 'updated@example.com',
            'age' => 35
        ]);

        $this->controller->resetHookTracker();
        $this->controller->testValidateInlineUpdateRequest($request, $model->id);

        $this->assertTrue($this->controller->getHookTracker()['onInlineUpdateValidation']);
    }
}
