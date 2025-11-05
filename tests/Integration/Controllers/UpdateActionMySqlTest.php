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
 * Test model for Update action MySQL integration testing
 */
class UpdateActionMySqlTestModel extends Model
{
    protected $table = 'update_action_mysql_test_models';
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
 * Test scaffolder for Update action MySQL integration testing
 */
class UpdateActionMySqlTestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'update-action-mysql-test';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name')->rules(['required', 'string', 'max:255']),
            Text::make('email')->rules(['required', 'email']),
            Number::make('age')->rules(['required', 'integer', 'min:0']),
        ];
    }

    protected function setModel(): string
    {
        return UpdateActionMySqlTestModel::class;
    }

    protected function setActions(): array
    {
        return Actions::all();
    }
}

/**
 * Test controller for Update action MySQL integration testing
 */
class UpdateActionMySqlTestController extends \Illuminate\Routing\Controller
{
    use \Tir\Crud\Controllers\Traits\CrudInit,
        \Tir\Crud\Controllers\Traits\Update;

    protected function setScaffolder(): string
    {
        return UpdateActionMySqlTestScaffolder::class;
    }

    public function callAction($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }
}

/**
 * Update Action MySQL Integration Test
 *
 * Tests the complete update action flow with MySQL database adapter
 */
class UpdateActionMySqlTest extends \Tir\Crud\Tests\TestCase
{
    use RefreshDatabase;

    private UpdateActionMySqlTestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('update_action_mysql_test_models', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->integer('age');
            $table->timestamps();
        });

        $this->controller = new UpdateActionMySqlTestController();
    }

    /**
     * Test MySQL adapter processes flat array structure in update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_updates_with_flat_array_structure()
    {
        // Create initial record
        $model = UpdateActionMySqlTestModel::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'age' => 30
        ]);

        // Update with flat structure
        $request = Request::create('/', 'PUT', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'age' => 35
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // MySQL adapter should have used flat structure
        $this->assertDatabaseHas('update_action_mysql_test_models', [
            'id' => $model->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'age' => 35
        ]);
    }

    /**
     * Test MySQL adapter filters fillable fields correctly during update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_filters_fillable_fields_on_update()
    {
        $model = UpdateActionMySqlTestModel::create([
            'name' => 'Original',
            'email' => 'original@example.com',
            'age' => 25
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'Updated',
            'email' => 'updated@example.com',
            'age' => 30,
            'extra_field' => 'should be filtered',
            'another_field' => 'also filtered'
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // Verify only fillable fields were updated
        $updatedModel = UpdateActionMySqlTestModel::find($model->id);
        $this->assertEquals('Updated', $updatedModel->name);
        $this->assertEquals('updated@example.com', $updatedModel->email);
        $this->assertEquals(30, $updatedModel->age);
        
        // Extra fields should not exist
        $this->assertFalse(isset($updatedModel->extra_field));
        $this->assertFalse(isset($updatedModel->another_field));
    }

    /**
     * Test MySQL adapter uses model fill() method during update
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mysql_adapter_uses_model_fill_method_on_update()
    {
        $model = UpdateActionMySqlTestModel::create([
            'name' => 'Before Fill',
            'email' => 'before@example.com',
            'age' => 20
        ]);

        $request = Request::create('/', 'PUT', [
            'name' => 'After Fill',
            'email' => 'after@example.com',
            'age' => 25
        ]);

        $response = $this->controller->update($request, $model->id);
        $data = $response->getData(true);

        $this->assertTrue($data['updated']);
        
        // Verify fill() method worked (respecting fillable protection)
        $updatedModel = UpdateActionMySqlTestModel::find($model->id);
        $this->assertEquals('After Fill', $updatedModel->name);
        $this->assertEquals('after@example.com', $updatedModel->email);
        $this->assertEquals(25, $updatedModel->age);
        
        // Verify fillable protection is still active
        $this->assertIsArray($updatedModel->getFillable());
        $this->assertContains('name', $updatedModel->getFillable());
        $this->assertContains('email', $updatedModel->getFillable());
        $this->assertContains('age', $updatedModel->getFillable());
    }
}
