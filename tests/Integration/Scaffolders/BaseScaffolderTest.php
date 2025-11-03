<?php

namespace Tir\Crud\Tests\Integration\Scaffolders;

use Tir\Crud\Tests\TestCase;
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Illuminate\Database\Eloquent\Model;
use Tir\Crud\Tests\Integration\Scaffolders\TestModels\TestModel;

/**
 * Test scaffolder implementation for testing purposes
 */
class TestScaffolder extends BaseScaffolder
{
    protected function setModuleName(): string
    {
        return 'test-module';
    }

    protected function setFields(): array
    {
        return [
            Text::make('name'),
            Text::make('email'),
        ];
    }

    protected function setModel(): string
    {
        return TestModel::class;
    }
}

class BaseScaffolderTest extends \Tir\Crud\Tests\TestCase
{
    /**
     * Test that scaffolder can be instantiated
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_scaffolder_can_be_instantiated()
    {
        $scaffolder = new TestScaffolder();

        $this->assertNotNull($scaffolder);
        $this->assertInstanceOf(TestScaffolder::class, $scaffolder);
    }

    /**
     * Test that module name is set correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_module_name_is_set_correctly()
    {
        $scaffolder = new TestScaffolder();

        $this->assertEquals('test-module', $scaffolder->getModuleName());
    }

    /**
     * Test that model class is returned correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_model_class_is_returned_correctly()
    {
        $scaffolder = new TestScaffolder();

        $this->assertEquals(TestModel::class, $scaffolder->modelClass());
    }

    /**
     * Test that scaffold method initializes the scaffolder correctly
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_scaffold_method_initializes_correctly()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel(['name' => 'John', 'email' => 'john@example.com']);

        $result = $scaffolder->scaffold('create', $model);

        $this->assertSame($scaffolder, $result); // Should return self for chaining
        $this->assertEquals($model, $scaffolder->model());
    }

    /**
     * Test that scaffold initializes fields handler
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_scaffold_initializes_fields_handler()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('create', $model);

        $fieldsHandler = $scaffolder->fieldsHandler();
        $this->assertNotNull($fieldsHandler);
        $this->assertInstanceOf(\Tir\Crud\Support\Scaffold\FieldsHandler::class, $fieldsHandler);
    }

    /**
     * Test that getCreateFields returns array from fields handler
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_create_fields_returns_array()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('create', $model);
        $fields = $scaffolder->getCreateFields();

        $this->assertIsArray($fields);
        $this->assertCount(2, $fields); // We defined 2 fields in setFields
    }

    /**
     * Test that getEditFields returns array from fields handler
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_edit_fields_returns_array()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('edit', $model);
        $fields = $scaffolder->getEditFields();

        $this->assertIsArray($fields);
        $this->assertCount(2, $fields);
    }

    /**
     * Test that getIndexFields returns array from fields handler
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_index_fields_returns_array()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('index', $model);
        $fields = $scaffolder->getIndexFields();

        $this->assertIsArray($fields);
        $this->assertCount(2, $fields);
    }

    /**
     * Test that getDetailFields returns array from fields handler
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_detail_fields_returns_array()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('detail', $model);
        $fields = $scaffolder->getDetailFields();

        $this->assertIsArray($fields);
        $this->assertCount(2, $fields);
    }

    /**
     * Test that getCreateScaffold returns complete scaffold array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_create_scaffold_returns_complete_structure()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('create', $model);
        $scaffold = $scaffolder->getCreateScaffold();

        $this->assertIsArray($scaffold);
        $this->assertArrayHasKey('fields', $scaffold);
        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertArrayHasKey('validationMsg', $scaffold);
        $this->assertArrayHasKey('configs', $scaffold);

        $this->assertIsArray($scaffold['fields']);
        $this->assertIsArray($scaffold['buttons']);
        $this->assertIsArray($scaffold['configs']);
    }

    /**
     * Test that getEditScaffold returns complete scaffold array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_edit_scaffold_returns_complete_structure()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('edit', $model);
        $scaffold = $scaffolder->getEditScaffold();

        $this->assertIsArray($scaffold);
        $this->assertArrayHasKey('fields', $scaffold);
        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertArrayHasKey('validationMsg', $scaffold);
        $this->assertArrayHasKey('configs', $scaffold);
    }

    /**
     * Test that getIndexScaffold returns complete scaffold array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_index_scaffold_returns_complete_structure()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('index', $model);
        $scaffold = $scaffolder->getIndexScaffold();

        $this->assertIsArray($scaffold);
        $this->assertArrayHasKey('fields', $scaffold);
        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertArrayHasKey('configs', $scaffold);
    }

    /**
     * Test that getDetailScaffold returns complete scaffold array
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_detail_scaffold_returns_complete_structure()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('detail', $model);
        $scaffold = $scaffolder->getDetailScaffold();

        $this->assertIsArray($scaffold);
        $this->assertArrayHasKey('fields', $scaffold);
        $this->assertArrayHasKey('buttons', $scaffold);
        $this->assertArrayHasKey('configs', $scaffold);
    }

    /**
     * Test that calling fieldsHandler before scaffold throws exception
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_fields_handler_throws_exception_before_scaffold()
    {
        $scaffolder = new TestScaffolder();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fields handler not initialized. Call scaffold() first.');

        $scaffolder->fieldsHandler();
    }

    /**
     * Test that calling getCreateFields before scaffold throws exception
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_create_fields_throws_exception_before_scaffold()
    {
        $scaffolder = new TestScaffolder();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fields handler not initialized. Call scaffold() first.');

        $scaffolder->getCreateFields();
    }

    /**
     * Test that scaffold can be called with null model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_scaffold_can_be_called_with_null_model()
    {
        $scaffolder = new TestScaffolder();

        $result = $scaffolder->scaffold('create', null);

        $this->assertSame($scaffolder, $result);
        $this->assertNull($scaffolder->model());
    }

    /**
     * Test that scaffold re-initializes when called with different parameters
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_scaffold_reinitializes_with_different_parameters()
    {
        $scaffolder = new TestScaffolder();
        $model1 = new TestModel(['name' => 'John']);
        $model2 = new TestModel(['name' => 'Jane']);

        $scaffolder->scaffold('create', $model1);
        $this->assertEquals($model1, $scaffolder->model());

        $scaffolder->scaffold('edit', $model2);
        $this->assertEquals($model2, $scaffolder->model());
    }

    /**
     * Test that getConfigs returns proper configuration structure
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_configs_returns_proper_structure()
    {
        $scaffolder = new TestScaffolder();
        $model = new TestModel();

        $scaffolder->scaffold('create', $model);

        // We need to access getConfigs indirectly through a scaffold method
        $scaffold = $scaffolder->getCreateScaffold();
        $configs = $scaffold['configs'];

        $this->assertIsArray($configs);
        $this->assertArrayHasKey('actions', $configs);
        $this->assertArrayHasKey('module_title', $configs);
        $this->assertArrayHasKey('primary_key', $configs);

        $this->assertEquals('test-module', $configs['module_title']);
        $this->assertEquals('id', $configs['primary_key']); // Default primary key
    }
}
