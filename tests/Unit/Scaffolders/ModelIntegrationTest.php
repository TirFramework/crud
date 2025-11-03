<?php

namespace Tir\Crud\Tests\Unit\Scaffolders;

use Tir\Crud\Support\Scaffold\Traits\ModelIntegration;
use Tir\Crud\Tests\TestCase;

/**
 * Test ModelIntegration trait functionality
 *
 * ModelIntegration provides convenient access to model properties
 * and helper methods for working with the current model in scaffolders.
 */
class ModelIntegrationTest extends TestCase
{
    /**
     * Test class that uses the ModelIntegration trait for testing
     */
    private $modelIntegrationInstance;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test class that uses the ModelIntegration trait
        $this->modelIntegrationInstance = new class {
            use ModelIntegration;

            // Make protected methods public for testing
            public function publicHasValue(string $property): bool
            {
                return $this->hasValue($property);
            }

            public function publicGetValue(string $property, $default = null)
            {
                return $this->getValue($property, $default);
            }

            public function publicCurrentModel()
            {
                return $this->currentModel();
            }

            // Helper to set current model for testing
            public function setCurrentModel($model): void
            {
                $this->currentModel = $model;
            }
        };
    }

    /**
     * Test magic __get method returns model property value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_magic_get_returns_model_property_value()
    {
        $model = new class {
            public $name = 'John Doe';
            public $email = 'john@example.com';
        };

        $this->modelIntegrationInstance->setCurrentModel($model);

        $this->assertEquals('John Doe', $this->modelIntegrationInstance->name);
        $this->assertEquals('john@example.com', $this->modelIntegrationInstance->email);
    }

    /**
     * Test magic __get method returns null for non-existent property
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_magic_get_returns_null_for_non_existent_property()
    {
        $model = new class {
            public $name = 'John Doe';
        };

        $this->modelIntegrationInstance->setCurrentModel($model);

        $this->assertNull($this->modelIntegrationInstance->nonexistent);
    }

    /**
     * Test magic __get method returns null when no model is set
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_magic_get_returns_null_when_no_model_is_set()
    {
        $this->assertNull($this->modelIntegrationInstance->name);
    }

    /**
     * Test magic __isset method returns true for existing properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_magic_isset_returns_true_for_existing_properties()
    {
        $model = new class {
            public $name = 'John Doe';
            public $email = null; // null but exists
        };

        $this->modelIntegrationInstance->setCurrentModel($model);

        $this->assertTrue(isset($this->modelIntegrationInstance->name));
        $this->assertFalse(isset($this->modelIntegrationInstance->email)); // null properties return false for isset
    }

    /**
     * Test magic __isset method returns false for non-existent properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_magic_isset_returns_false_for_non_existent_properties()
    {
        $model = new class {
            public $name = 'John Doe';
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertFalse(isset($this->modelIntegrationInstance->nonexistent));
    }

    /**
     * Test magic __isset method returns false when no model is set
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_magic_isset_returns_false_when_no_model_is_set()
    {
        $this->assertFalse(isset($this->modelIntegrationInstance->name));
    }

    /**
     * Test hasValue method returns true for set properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_has_value_returns_true_for_set_properties()
    {
        $model = new class {
            public $name = 'John Doe';
            public $email = 'john@example.com';
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertTrue($this->modelIntegrationInstance->publicHasValue('name'));
        $this->assertTrue($this->modelIntegrationInstance->publicHasValue('email'));
    }

    /**
     * Test hasValue method returns false for null properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_has_value_returns_false_for_null_properties()
    {
        $model = new class {
            public $name = 'John Doe';
            public $email = null;
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertTrue($this->modelIntegrationInstance->publicHasValue('name'));
        $this->assertFalse($this->modelIntegrationInstance->publicHasValue('email'));
    }

    /**
     * Test hasValue method returns false for non-existent properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_has_value_returns_false_for_non_existent_properties()
    {
        $model = new class {
            public $name = 'John Doe';
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertFalse($this->modelIntegrationInstance->publicHasValue('nonexistent'));
    }

    /**
     * Test hasValue method returns false when no model is set
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_has_value_returns_false_when_no_model_is_set()
    {
        $this->assertFalse($this->modelIntegrationInstance->publicHasValue('name'));
    }

    /**
     * Test getValue method returns property value
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_value_returns_property_value()
    {
        $model = new class {
            public $name = 'John Doe';
            public $email = 'john@example.com';
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertEquals('John Doe', $this->modelIntegrationInstance->publicGetValue('name'));
        $this->assertEquals('john@example.com', $this->modelIntegrationInstance->publicGetValue('email'));
    }

    /**
     * Test getValue method returns default value for non-existent properties
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_value_returns_default_value_for_non_existent_properties()
    {
        $model = new class {
            public $name = 'John Doe';
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertEquals('default_value', $this->modelIntegrationInstance->publicGetValue('nonexistent', 'default_value'));
    }

    /**
     * Test getValue method returns null when no model is set
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_get_value_returns_null_when_no_model_is_set()
    {
        $this->assertNull($this->modelIntegrationInstance->publicGetValue('name'));
    }

    /**
     * Test currentModel method returns the current model
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_current_model_method_returns_the_current_model()
    {
        $model = new class {
            public $name = 'John Doe';
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        $this->assertSame($model, $this->modelIntegrationInstance->publicCurrentModel());
    }

    /**
     * Test currentModel method returns null when no model is set
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_current_model_method_returns_null_when_no_model_is_set()
    {
        $this->assertNull($this->modelIntegrationInstance->publicCurrentModel());
    }

    /**
     * Test with Laravel Eloquent model simulation
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function test_with_laravel_eloquent_model_simulation()
    {
        // Simulate a Laravel Eloquent model
        $model = new class {
            private $attributes = [
                'name' => 'John Doe',
                'email' => null, // null but defined
                'status' => 'active'
            ];

            public function getAttributes()
            {
                return $this->attributes;
            }

            public function __get($key)
            {
                return $this->attributes[$key] ?? null;
            }

            public function __isset($key)
            {
                return array_key_exists($key, $this->attributes);
            }
        };

        // Set the current model using reflection
        $reflection = new \ReflectionClass($this->modelIntegrationInstance);
        $property = $reflection->getProperty('currentModel');
        $property->setAccessible(true);
        $property->setValue($this->modelIntegrationInstance, $model);

        // Test magic methods work with Eloquent-like models
        $this->assertEquals('John Doe', $this->modelIntegrationInstance->name);
        $this->assertNull($this->modelIntegrationInstance->email); // null but defined
        $this->assertEquals('active', $this->modelIntegrationInstance->status);

        // Test isset works
        $this->assertTrue(isset($this->modelIntegrationInstance->name));
        $this->assertTrue(isset($this->modelIntegrationInstance->email)); // null but defined in Eloquent
        $this->assertFalse(isset($this->modelIntegrationInstance->nonexistent));

        // Test hasValue works
        $this->assertTrue($this->modelIntegrationInstance->publicHasValue('name'));
        $this->assertTrue($this->modelIntegrationInstance->publicHasValue('email')); // null but defined in Eloquent
        $this->assertTrue($this->modelIntegrationInstance->publicHasValue('status'));
    }
}
