<?php

namespace Tir\Crud\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tir\Crud\CrudServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Publish config
        $this->publishConfig();
    }

    protected function getPackageProviders($app)
    {
        return [
            CrudServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function publishConfig()
    {
        $this->artisan('vendor:publish', [
            '--provider' => 'Tir\\Crud\\CrudServiceProvider',
            '--force' => true,
        ]);
    }
}
