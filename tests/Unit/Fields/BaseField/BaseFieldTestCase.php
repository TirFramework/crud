<?php

namespace Tir\Crud\Tests\Unit\Fields\BaseField;

use ReflectionProperty;
use Tir\Crud\Tests\TestCase;

/**
 * Base test class for all BaseField tests
 *
 * Provides common helper methods for testing field properties
 */
abstract class BaseFieldTestCase extends TestCase
{
    /**
     * Helper method to get protected property value using Reflection
     *
     * This allows us to test protected properties without exposing them
     * in the public API. Useful for verifying internal state.
     *
     * @param object $object The object to inspect
     * @param string $property The property name to access
     * @return mixed The property value
     */
    protected function getPropertyValue(object $object, string $property): mixed
    {
        $reflection = new ReflectionProperty($object::class, $property);
        $reflection->setAccessible(true);
        return $reflection->getValue($object);
    }
}
